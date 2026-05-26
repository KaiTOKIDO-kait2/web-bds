from __future__ import annotations

import logging
import re
from typing import Optional

import unicodedata

from sqlalchemy.exc import OperationalError, ProgrammingError
from sqlalchemy import text

from app.behavior_events import log_behavior_event
from app.config import get_settings
from app.db import get_engine, session_scope
from app.deepseek_client import extract_with_deepseek
from app.merge_filters import merge_search_filters
from app.normalize import normalize_user_text
from app.property_search import search_with_fallback
from app.rules_extract import (
    extract_keyword_guess,
    extract_rules,
    is_smalltalk,
    wants_cheapest,
    wants_priciest,
)
from app.semantic_extract import extract_semantic_filters
from app.schemas import AmenitiesFilter, ChatMessageResponse, SearchFilters, SuggestionsResponse
from app.pyd_compat import model_to_dict, model_with_update
from app.session_repo import add_message, get_or_create_session, load_context_filters, reset_context, save_context


logger = logging.getLogger(__name__)


def _location_label(f: SearchFilters) -> str:
    if f.ward_id:
        return "khu vực đã chọn"
    if f.city_id:
        return "thành phố đã chọn"
    if f.keyword:
        return f"\"{f.keyword}\""
    return ""


def _compute_missing_geo(f: SearchFilters) -> list[str]:
    has_geo = bool(
        f.ward_id
        or f.city_id
        or (f.ward_name_hint and len(f.ward_name_hint.strip()) >= 2)
        or (f.keyword and len(f.keyword.strip()) >= 2)
        or f.limit_to_pids
    )
    if not has_geo:
        return ["location"]
    return []


def _clarify_message(missing: list[str]) -> tuple[str, list[str]]:
    parts: list[str] = []
    qs: list[str] = []
    if "location" in missing:
        parts.append(
            "Mình chưa xác định được khu vực bạn muốn tìm."
        )
        qs.append("Bạn cho mình biết phường / xã hoặc tên đường nhé? (Ví dụ: Phường Bình Thạnh, Phường 1, Hà Nội)")
    if not parts:
        parts.append("Bạn mô tả thêm tiêu chí để mình tìm chính xác hơn nhé.")
    return " ".join(parts), qs


def _normalize_place_text(value: str) -> str:
    text = unicodedata.normalize("NFD", (value or "").lower())
    text = "".join(c for c in text if unicodedata.category(c) != "Mn")
    text = re.sub(r"[^a-z0-9\s]", " ", text)
    return re.sub(r"\s+", " ", text).strip()


_WARD_RELATIVE_HINT_RE = re.compile(
    r"^\s*(ph\u01b0\u1eddng|x\u00e3|khu)\s+(kh\u00e1c|kia|n\u00e0o|\u0111\u00f3|\u0111\u1ea5y|n\u1ecd|\u0111\u00e2y|n\u1eefa)\s*$",
    re.IGNORECASE,
)


def _is_strong_substring(a: str, b: str) -> bool:
    """True nếu a là substring của b hoặc ngược lại, VÀ phần ngắn >= 70% phần dài."""
    if not a or not b:
        return False
    if a in b or b in a:
        shorter, longer = (a, b) if len(a) <= len(b) else (b, a)
        return len(shorter) >= len(longer) * 0.7
    return False


def _place_core_text(value: str) -> str:
    text = _normalize_place_text(value)
    stopwords = {
        "thanh",
        "pho",
        "tp",
        "tinh",
        "xa",
        "phuong",
        "thi",
        "tran",
        "khu",
        "vuc",
        "khac",
    }
    parts = [part for part in text.split() if part not in stopwords]
    return " ".join(parts)


def _has_location_signal(text: str) -> bool:
    normalized = _place_core_text(text)
    return bool(
        re.search(r"\b(thanh pho|tp|hcm|ha noi|hai phong|phuong|xa)\b", normalized)
        or re.search(r"\b(hồ chí minh|hà nội|hải phòng|sài gòn)\b", text.lower())
    )


def _has_ward_override_signal(text: str) -> bool:
    """Phát hiện user muốn đổi sang khu vực / phường / xã khác."""
    t = text.lower()
    return bool(
        re.search(r"\b(chuy[eê]n|đổi|sang|qua)\s+.{0,20}(xã|phường|khu|nơi|chỗ|vùng)", t)
        or re.search(r"\b(xã khác|phường khác|khu khác|nơi khác|chỗ khác|khu vực khác|vùng khác)\b", t)
    )


def _has_property_type_override_signal(text: str) -> bool:
    """Phát hiện user muốn đổi loại BĐS (thay thế, không cộng thêm)."""
    t = text.lower()
    return bool(
        re.search(r"\b(thôi|chuy[eê]n|đổi)\s+sang\b", t)
        or re.search(r"\bchỉ\s+(tìm|xem)\b", t)
    )


def _has_amenity_override_signal(text: str) -> bool:
    """
    Phát hiện user muốn THAY THẾ toàn bộ amenities bằng yêu cầu mới.
    Dấu hiệu: 'chỉ cần X thôi', 'chỉ cần X', 'thôi chỉ cần', 'không cần Y nữa', 'bỏ Y đi'.
    """
    t = text.lower()
    return bool(
        re.search(r"\bchỉ\s+cần\b", t)
        or re.search(r"\bkhông\s+cần.{1,30}nữa\b", t)
        or re.search(r"\bbỏ.{1,20}\b(đi|rồi)\b", t)
        or re.search(r"\bthôi\s+.*\s+thôi\b", t)
    )

#hàm lấy id của city
def _resolve_city_id_from_text(db, raw_text: str, normalized_text: str) -> Optional[int]:
    txt_core = _place_core_text(raw_text + " " + normalized_text)
    if not txt_core:
        return None

    # alias ngắn cho viết tắt phổ biến
    if re.search(r"\b(hcm|tphcm|tp hcm)\b", txt_core):
        row = db.execute(
            text("SELECT cid FROM city WHERE cname LIKE :name ORDER BY cid ASC LIMIT 1"),
            {"name": "%Hồ Chí Minh%"},
        ).first()
        if row:
            return int(row[0])
    if re.search(r"\b(hn)\b", txt_core):
        row = db.execute(
            text("SELECT cid FROM city WHERE cname LIKE :name ORDER BY cid ASC LIMIT 1"),
            {"name": "%Hà Nội%"},
        ).first()
        if row:
            return int(row[0])

    rows = db.execute(
        text("SELECT cid, cname FROM city ORDER BY LENGTH(cname) DESC, cid ASC")
    ).mappings().all()
    for row in rows:
        city_core = _place_core_text(str(row.get("cname") or ""))
        if city_core and len(city_core) >= 3 and re.search(r"\b" + re.escape(city_core) + r"\b", txt_core):
            return int(row["cid"])

    return None


def _resolve_ward_id_from_text(db, city_id: Optional[int], raw_text: str, normalized_text: str) -> Optional[int]:
    txt = raw_text.strip().lower()

    # Chỉ resolve ward khi user có cue rõ ràng kiểu "phường/xã ...".
    cue_match = re.search(r"\b(phường|xã)\s+([a-zà-ỹ0-9]+(?:\s+[a-zà-ỹ0-9]+){0,4})", txt, flags=re.IGNORECASE)
    if cue_match:
        ward_name = (cue_match.group(2) or "").strip()
        ward_name = re.split(r"[,.;\n]", ward_name)[0].strip()
        ward_name = re.split(r"\b(thành phố|tp\.?|tỉnh)\b", ward_name, maxsplit=1, flags=re.IGNORECASE)[0].strip()
        ward_name = re.sub(r"\s+\b(có|và|gần|cách|với|để|không|nha|nhé|nhe|ko|ạ|a|càng|thì|là|hoặc|nếu)\b.*$", "", ward_name, flags=re.IGNORECASE).strip()
        ward_core = _place_core_text(ward_name)

        logger.debug(
            "ward_resolve cue_match ward_name=%r ward_core=%r city_id=%s",
            ward_name, ward_core, city_id,
        )

        if ward_core:
            rows = db.execute(
                text("SELECT wid, city_id, wname FROM wards ORDER BY LENGTH(wname) DESC, wid ASC")
            ).mappings().all()

            # 1) Phân loại exact match theo city
            city_exact: list[int] = []
            all_exact: list[int] = []
            for row in rows:
                row_core = _place_core_text(str(row.get("wname") or ""))
                if row_core == ward_core:
                    all_exact.append(int(row["wid"]))
                    if city_id is not None and int(row.get("city_id") or 0) == int(city_id):
                        city_exact.append(int(row["wid"]))

            logger.debug(
                "ward_resolve exact city_exact=%s all_exact=%s ward_core=%r",
                city_exact, all_exact, ward_core,
            )

            if city_exact:
                return city_exact[0]  # duy nhất trong city đã biết
            if len(all_exact) == 1:
                return all_exact[0]   # duy nhất toàn cục → an toàn
            if len(all_exact) > 1:
                # Ward trùng tên nhiều tỉnh nhưng không thuộc tỉnh cũ
                # → User muốn chuyển vùng, chọn match đầu tiên (city sẽ auto-correct)
                if city_id is not None:
                    logger.debug("ward_resolve picking first exact cross-province wid=%s", all_exact[0])
                    return all_exact[0]
                return None           # chưa có city context → thực sự ambiguous

            # 2) fallback substring trong cùng city (chỉ khi overlap đáng kể)
            for row in rows:
                row_core = _place_core_text(str(row.get("wname") or ""))
                if row_core and _is_strong_substring(row_core, ward_core):
                    if city_id is not None and int(row.get("city_id") or 0) == int(city_id):
                        logger.debug("ward_resolve substring in-city wid=%s row_core=%r", row["wid"], row_core)
                        return int(row["wid"])

            # 3) fallback substring không lọc city — chỉ dùng khi duy nhất 1 match
            sub_all: list[tuple[int, str]] = []
            for row in rows:
                row_core = _place_core_text(str(row.get("wname") or ""))
                if row_core and _is_strong_substring(row_core, ward_core):
                    sub_all.append((int(row["wid"]), row_core))
            if len(sub_all) == 1:
                logger.debug("ward_resolve substring global unique wid=%s core=%r", sub_all[0][0], sub_all[0][1])
                return sub_all[0][0]
            if sub_all:
                logger.debug("ward_resolve substring global ambiguous count=%s first=%s", len(sub_all), sub_all[:3])
            return None

    # Nếu không có cue phường/xã, không tự đoán ward từ city/text chung nữa.
    txt_num = txt
    m = re.search(r"(?:phường|xã|khu vực)\s*(\d{1,2})", txt_num)
    if m:
        num = int(m.group(1))
        candidates = [f"phường {num}", f"xã {num}"]
        for cand in candidates:
            row = db.execute(
                text("SELECT wid FROM wards WHERE LOWER(wname) LIKE :n ORDER BY wid ASC LIMIT 1"),
                {"n": "%" + cand.lower() + "%"},
            ).first()
            if row:
                return int(row[0])
    return None


def handle_chat_message(
    public_session_id: str,
    user_text: str,
    user_uid: Optional[int],
) -> ChatMessageResponse:
    settings = get_settings()
    raw = (user_text or "").strip()
    if len(raw) > settings.max_user_text_len:
        raw = raw[: settings.max_user_text_len]

    normalized = normalize_user_text(raw)
    logger.debug("chat.message input session_id=%s user_uid=%s raw=%r normalized=%r", public_session_id, user_uid, raw, normalized)

    if is_smalltalk(raw):
        reply = (
            "Chào bạn! Mình là trợ lý tìm kiếm bất động sản. "
            "Bạn có thể nói theo kiểu: \"Căn hộ 2 phòng ngủ Phường Bình Thạnh dưới 20 triệu có hồ bơi\"."
        )
        return ChatMessageResponse(
            reply_text=reply,
            intent="smalltalk",
            filters=SearchFilters(stype="rent"),
            missing_slots=[],
            follow_up_questions=[],
            result_count=0,
            properties=[],
            session_id=public_session_id,
        )

    try:
        with session_scope() as db:
            sid_pk = get_or_create_session(db, public_session_id, user_uid)
            prev_filters, last_pids = load_context_filters(db, sid_pk)
            # limit_to_pids, exclude_ward_ids là filter tạm thời, không kế thừa giữa các turn.
            if prev_filters.limit_to_pids:
                prev_filters = model_with_update(prev_filters, limit_to_pids=[])
            if prev_filters.exclude_ward_ids:
                prev_filters = model_with_update(prev_filters, exclude_ward_ids=[])

            rules = extract_rules(normalized)
            kw_guess = extract_keyword_guess(normalized)
            if kw_guess and not rules.get("keyword") and not rules.get("ward_id") and not rules.get("ward_name_hint"):
                rules["keyword"] = kw_guess

            llm_payload = extract_with_deepseek(normalized, current_filters=model_to_dict(prev_filters))
            semantic_payload = extract_semantic_filters(normalized)
            # City/Ward IDs từ LLM không đáng tin bằng DB resolver, nên bỏ luôn.
            if isinstance(llm_payload, dict):
                llm_payload = dict(llm_payload)
                llm_payload.pop("city_id", None)
                llm_payload.pop("ward_id", None)

            if _has_location_signal(raw):
                prev_filters = model_with_update(prev_filters, city_id=None, ward_id=None)

            if _has_ward_override_signal(raw) or _has_ward_override_signal(normalized):
                _old_ward_for_exclude = prev_filters.ward_id
                prev_filters = model_with_update(
                    prev_filters,
                    ward_id=None, ward_name_hint=None, keyword=None,
                    exclude_ward_ids=[_old_ward_for_exclude] if _old_ward_for_exclude else [],
                )
                logger.debug("chat.message ward override detected — cleared ward, exclude_old=%s", _old_ward_for_exclude)

            if _has_property_type_override_signal(raw) or _has_property_type_override_signal(normalized):
                prev_filters = model_with_update(prev_filters, property_types=[])
                logger.debug("chat.message property_type override detected — cleared types from context")

            if _has_amenity_override_signal(raw) or _has_amenity_override_signal(normalized):
                prev_filters = model_with_update(prev_filters, amenities=AmenitiesFilter())
                logger.debug("chat.message amenity override detected — cleared amenities from context")

            merged = merge_search_filters(prev_filters, rules, semantic_payload, llm_payload)

            # Post-merge: xóa ward_name_hint nếu là từ tương đối (vd: "Phường Khác", "Xã Nào")
            if merged.ward_name_hint and _WARD_RELATIVE_HINT_RE.match(merged.ward_name_hint):
                kw = merged.keyword
                merged = model_with_update(
                    merged,
                    ward_name_hint=None,
                    keyword=None if (kw and _WARD_RELATIVE_HINT_RE.match(kw)) else kw,
                )
                logger.debug("chat.message cleared relative ward_name_hint=%r", merged.ward_name_hint)

            logger.debug(
                "chat.message merged-before-db session_id=%s prev=%s rules=%s semantic=%s llm=%s merged=%s",
                public_session_id,
                model_to_dict(prev_filters),
                rules,
                semantic_payload,
                llm_payload.model_dump() if hasattr(llm_payload, "model_dump") else llm_payload,
                model_to_dict(merged),
            )
            city_id_from_db = _resolve_city_id_from_text(db, raw, normalized)
            _city_change_requested = _has_location_signal(raw) or _has_location_signal(normalized)
            # Nếu resolve được city từ text VÀ khác city hiện tại → user đang chuyển tỉnh
            _city_differs = (city_id_from_db is not None and merged.city_id is not None and city_id_from_db != merged.city_id)
            if city_id_from_db is not None:
                if merged.city_id is None or _city_change_requested or _city_differs:
                    _old_city = merged.city_id
                    merged = model_with_update(merged, city_id=city_id_from_db)
                    logger.debug("chat.message resolved city_id=%s from text=%r", city_id_from_db, raw)

                    # Khi city thay đổi → kiểm tra ward cũ có thuộc city mới không
                    if _old_city != city_id_from_db and merged.ward_id is not None:
                        _ward_check = db.execute(
                            text("SELECT city_id FROM wards WHERE wid = :wid LIMIT 1"),
                            {"wid": int(merged.ward_id)},
                        ).first()
                        _ward_city = int(_ward_check[0]) if _ward_check and _ward_check[0] else None
                        if _ward_city != city_id_from_db:
                            logger.info(
                                "chat.message city changed %s→%s, clearing ward_id=%s (belongs to city %s)",
                                _old_city, city_id_from_db, merged.ward_id, _ward_city,
                            )
                            merged = model_with_update(merged, ward_id=None, ward_name_hint=None)
                else:
                    logger.debug(
                        "chat.message city_id_from_db=%s ignored — existing city_id=%s, no explicit change signal",
                        city_id_from_db, merged.city_id,
                    )
            else:
                logger.debug("chat.message city unresolved from text=%r", raw)

            # resolve ward_id theo tên thực trong DB, rồi lấy city_id của ward để tránh giữ context cũ sai
            _prev_city_id = merged.city_id
            _city_switched_note = ""
            ward_id = _resolve_ward_id_from_text(db, merged.city_id, raw, normalized)
            # Verification: cross-check resolved ward name against user input
            if ward_id is not None:
                _verify = db.execute(
                    text("SELECT wname FROM wards WHERE wid = :wid LIMIT 1"),
                    {"wid": int(ward_id)},
                ).first()
                if _verify:
                    _resolved_core = _place_core_text(str(_verify[0] or ""))
                    _input_core = _place_core_text(raw)
                    if _resolved_core and _resolved_core not in _input_core and _input_core not in _resolved_core:
                        # Cũng kiểm tra từng từ của resolved_core có xuất hiện trong input không
                        _resolved_words = set(_resolved_core.split())
                        _input_words = set(_input_core.split())
                        if not _resolved_words.issubset(_input_words):
                            logger.warning(
                                "ward_resolve REJECTED wid=%s wname=%r resolved_core=%r not found in input_core=%r",
                                ward_id, _verify[0], _resolved_core, _input_core,
                            )
                            ward_id = None
            if ward_id is not None:
                merged = model_with_update(merged, ward_id=ward_id)
                ward_row = db.execute(
                    text("SELECT city_id FROM wards WHERE wid = :wid LIMIT 1"),
                    {"wid": int(ward_id)},
                ).first()
                if ward_row and ward_row[0] is not None:
                    _new_city_id = int(ward_row[0])
                    merged = model_with_update(merged, city_id=_new_city_id)
                    logger.debug(
                        "chat.message resolved ward_id=%s and forced city_id=%s from wards table",
                        ward_id,
                        _new_city_id,
                    )
                    # Detect city switch for UX feedback
                    if _prev_city_id is not None and _prev_city_id != _new_city_id:
                        _old_cn = db.execute(
                            text("SELECT cname FROM city WHERE cid = :cid LIMIT 1"),
                            {"cid": int(_prev_city_id)},
                        ).first()
                        _new_cn = db.execute(
                            text("SELECT cname FROM city WHERE cid = :cid LIMIT 1"),
                            {"cid": int(_new_city_id)},
                        ).first()
                        _old_name = str(_old_cn[0]) if _old_cn else f"Tỉnh #{_prev_city_id}"
                        _new_name = str(_new_cn[0]) if _new_cn else f"Tỉnh #{_new_city_id}"
                        _ward_label = merged.ward_name_hint or f"khu vực #{ward_id}"
                        _city_switched_note = (
                            f"Mình thấy {_ward_label} thuộc {_new_name}, "
                            f"nên đã chuyển vùng tìm kiếm từ {_old_name} sang {_new_name}."
                        )
                        logger.info("chat.message city switched %s → %s due to ward resolve", _old_name, _new_name)
                else:
                    logger.warning("chat.message ward_id=%s resolved but ward city_id missing", ward_id)
            else:
                logger.debug("chat.message ward unresolved from text=%r city_id=%s", raw, merged.city_id)

            comparison_mode: Optional[str] = None
            if wants_cheapest(normalized) or wants_cheapest(raw):
                if last_pids:
                    comparison_mode = "cheapest"
                    merged = model_with_update(
                        merged,
                        limit_to_pids=last_pids,
                        sort="price_asc",
                        keyword=None,
                    )
                else:
                    msg = (
                        "Để chọn \"căn rẻ nhất\", bạn cần tìm danh sách trước. "
                        "Hãy cho mình biết khu vực và loại nhà (ví dụ: căn hộ 2 phòng ngủ Phường Bình Thạnh)."
                    )
                    add_message(db, sid_pk, "user", raw)
                    add_message(db, sid_pk, "assistant", msg)
                    return ChatMessageResponse(
                        reply_text=msg,
                        intent="clarify",
                        filters=merged,
                        missing_slots=["prior_results"],
                        follow_up_questions=[
                            "Căn hộ 2 phòng ngủ Phường Bình Thạnh dưới 20 triệu",
                            "Nhà nguyên căn Bình Thạnh có bãi xe",
                        ],
                        result_count=0,
                        properties=[],
                        session_id=public_session_id,
                    )

            if wants_priciest(normalized) or wants_priciest(raw):
                if last_pids:
                    comparison_mode = "priciest"
                    merged = model_with_update(
                        merged,
                        limit_to_pids=last_pids,
                        sort="price_desc",
                        keyword=None,
                    )

            missing = _compute_missing_geo(merged)
            if missing:
                reply, qs = _clarify_message(missing)
                add_message(db, sid_pk, "user", raw)
                add_message(
                    db,
                    sid_pk,
                    "assistant",
                    reply,
                    intent_json={"intent": "clarify", "missing": missing, "filters": model_to_dict(merged)},
                )
                return ChatMessageResponse(
                    reply_text=reply,
                    intent="clarify",
                    filters=merged,
                    missing_slots=missing,
                    follow_up_questions=qs,
                    result_count=0,
                    properties=[],
                    session_id=public_session_id,
                )

            engine = get_engine()
            cards, total, fb_level, fb_note, context_pids = search_with_fallback(engine, merged, query_text=raw)
            if comparison_mode and cards:
                cards = cards[:1]

            save_context(db, sid_pk, merged, context_pids if total > 0 else [])
            logger.debug(
                "chat.message final filters session_id=%s filters=%s result_count=%s fallback_level=%s",
                public_session_id,
                model_to_dict(merged),
                total,
                fb_level,
            )

            follow_ups: list[str] = []
            if total == 0:
                hints: list[str] = []
                am_dict = model_to_dict(merged.amenities) if merged.amenities else {}
                active_am = [k for k, v in am_dict.items() if v is True]
                if active_am:
                    hints.append("bỏ bớt tiêu chí tiện ích")
                    follow_ups.append("Tìm không cần " + ", ".join(active_am[:2]))
                if merged.ward_id or merged.ward_name_hint:
                    hints.append("mở rộng khu vực")
                    follow_ups.append("Tìm không giới hạn phường/xã cụ thể")
                if merged.property_types:
                    hints.append("thử loại BĐS khác")
                    follow_ups.append("Thử tìm " + ("Chung cư" if "Nhà" in merged.property_types else "Nhà"))
                if merged.bedrooms_min and merged.bedrooms_min > 1:
                    hints.append(f"giảm số phòng ngủ xuống {merged.bedrooms_min - 1}")
                    follow_ups.append(f"Tìm {merged.bedrooms_min - 1} phòng ngủ")
                if merged.price_max_million:
                    hints.append("nới thêm ngân sách")
                    follow_ups.append(f"Tìm dưới {int(merged.price_max_million * 1.3)} triệu")
                else:
                    hints.append("cho biết ngân sách tối đa")
                    follow_ups.append("Ngân sách tối đa của bạn khoảng bao nhiêu triệu mỗi tháng?")

                if merged.city_id and not (merged.ward_id or merged.ward_name_hint):
                    hints.append("đổi tỉnh/thành khác")
                    follow_ups.append("Tìm trên toàn quốc không giới hạn tỉnh/thành")
                hint_str = "; ".join(hints) if hints else "đổi khu vực hoặc bỏ bớt tiêu chí"
                reply = f"Hiện chưa tìm thấy tin phù hợp. Bạn thử {hint_str} nhé."
            else:
                if comparison_mode and cards:
                    card = cards[0]
                    label = "rẻ nhất" if comparison_mode == "cheapest" else "đắt nhất"
                    reply = (
                        f"Căn {label} trong danh sách vừa rồi là: "
                        f"{card.title} - {card.price_raw}."
                    )
                    follow_ups.append("Xem các căn tương tự")
                    follow_ups.append("Mở rộng khu vực tìm kiếm")
                else:
                    loc = _location_label(merged)
                    reply = f"Tìm thấy {total} tin" + (f" tại {loc}" if loc else "") + "."
                    if fb_note:
                        reply += " " + fb_note
                    if merged.sort == "price_asc" and merged.price_max_million is None:
                        reply += " Mình đã ưu tiên các tin có giá thấp trước."
                    if total <= 3:
                        follow_ups.append("Mở rộng khu vực tìm kiếm")
                        follow_ups.append("Thử loại BĐS khác")
                        if merged.amenities and any(
                            v for v in model_to_dict(merged.amenities).values() if v is True
                        ):
                            follow_ups.append("Bỏ bớt tiêu chí tiện ích để có thêm kết quả")
                    if merged.price_max_million is None:
                        budget_q = "Ngân sách tối đa của bạn khoảng bao nhiêu triệu mỗi tháng?"
                        if budget_q not in follow_ups:
                            follow_ups.append(budget_q)

            # Prepend city-switch UX note
            if _city_switched_note:
                reply = _city_switched_note + " " + reply

            add_message(db, sid_pk, "user", raw)
            add_message(
                db,
                sid_pk,
                "assistant",
                reply,
                intent_json={
                    "intent": "property_search",
                    "filters": model_to_dict(merged),
                    "result_count": total,
                    "fallback_level": fb_level,
                },
            )
            for pos, card in enumerate(cards, start=1):
                try:
                    log_behavior_event(
                        db,
                        event_type="chat_result_impression",
                        property_id=card.pid,
                        user_uid=user_uid,
                        public_session_id=public_session_id,
                        source="chatbot",
                        metadata={
                            "position": pos,
                            "ranking_score": card.ranking_score,
                            "semantic_score": card.semantic_score,
                        },
                    )
                except Exception as exc:
                    logger.warning("chat.event impression skipped: %s", exc)

            return ChatMessageResponse(
                reply_text=reply,
                intent="property_search",
                filters=merged,
                missing_slots=[],
                follow_up_questions=follow_ups,
                result_count=total,
                fallback_level=fb_level,
                fallback_note=fb_note,
                properties=cards,
                session_id=public_session_id,
            )

    except (OperationalError, ProgrammingError):
        err = "Hệ thống chưa kết nối được cơ sở dữ liệu hoặc thiếu bảng chatbot. Hãy import file DATABASE FILE/chatbot_schema.sql và kiểm tra .env."
        return ChatMessageResponse(
            reply_text=err,
            intent="unknown",
            filters=SearchFilters(stype="rent"),
            missing_slots=[],
            follow_up_questions=[],
            result_count=0,
            properties=[],
            session_id=public_session_id,
        )


def handle_reset(public_session_id: str, user_uid: Optional[int]) -> None:
    with session_scope() as db:
        sid_pk = get_or_create_session(db, public_session_id, user_uid)
        reset_context(db, sid_pk)


def handle_suggestions(public_session_id: str, user_uid: Optional[int]) -> SuggestionsResponse:
    with session_scope() as db:
        sid_pk = get_or_create_session(db, public_session_id, user_uid)
        f, _ = load_context_filters(db, sid_pk)

        city_name = None
        ward_name = None
        if f.city_id is not None:
            row = db.execute(text("SELECT cname FROM city WHERE cid = :cid LIMIT 1"), {"cid": int(f.city_id)}).first()
            if row:
                city_name = str(row[0] or "") or None
        if f.ward_id is not None:
            row = db.execute(text("SELECT wname FROM wards WHERE wid = :wid LIMIT 1"), {"wid": int(f.ward_id)}).first()
            if row:
                ward_name = str(row[0] or "") or None

    place = ward_name or f.ward_name_hint or city_name
    place_txt = f" {place}" if place else ""

    # động theo context hiện tại: ưu tiên type + bedrooms + price + amenities
    t0 = (f.property_types[0] if f.property_types else "Căn hộ")
    bed = int(f.bedrooms_min or 2)
    pmax = float(f.price_max_million or 20)
    smin = int(f.size_min or 0)
    size_txt = f" {max(30, smin)}m2" if smin else ""

    sug: list[str] = []
    sug.append(f"{t0} {bed} phòng ngủ{place_txt} dưới {int(pmax)} triệu{size_txt}")
    sug.append(f"{t0} {max(1, bed-1)} phòng ngủ{place_txt} dưới {int(max(8, pmax*0.8))} triệu")
    sug.append(f"{t0} {bed} phòng ngủ{place_txt} có bãi xe, an ninh")
    return SuggestionsResponse(suggestions=sug)
