from __future__ import annotations

import copy
import math
from datetime import datetime
from typing import Any, Optional

from sqlalchemy import text
from sqlalchemy.engine import Engine

from app.ai_vectors import cosine, encode_text, query_text_from_filters, vector_from_json
from app.config import get_settings
from app.pyd_compat import model_parse, model_to_dict
from app.schemas import AmenitiesFilter, PropertyCard, SearchFilters

ALLOWED_PROPERTY_TYPES = frozenset(
    {
        "Căn hộ",
        "Chung cư",
        "Nhà",
        "Biệt thự",
        "Văn phòng",
        "Tòa nhà",
        "Studio",
        "Mặt bằng",
    }
)

# Chỉ cần nhận diện giá dạng số (triệu) — tránh "Thỏa thuận"
PRICE_NUMERIC = "p.price REGEXP '^[0-9]'"

_SCHEMA_CACHE: dict[str, bool] = {}


def _schema_has(engine: Engine, kind: str, name: str) -> bool:
    cache_key = f"{kind}:{name}"
    if cache_key in _SCHEMA_CACHE:
        return _SCHEMA_CACHE[cache_key]
    with engine.connect() as conn:
        if kind == "table":
            sql = text(
                "SELECT 1 FROM information_schema.tables "
                "WHERE table_schema = DATABASE() AND table_name = :name LIMIT 1"
            )
        else:
            tbl, col = name.split(".", 1)
            sql = text(
                "SELECT 1 FROM information_schema.columns "
                "WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = :col LIMIT 1"
            )
            row = conn.execute(sql, {"tbl": tbl, "col": col}).first()
            ok = row is not None
            if ok or tbl not in {"property_embedding", "chatbot_event"}:
                _SCHEMA_CACHE[cache_key] = ok
            return ok
        row = conn.execute(sql, {"name": name}).first()
        ok = row is not None
        if ok or name not in {"property_embedding", "chatbot_event"}:
            _SCHEMA_CACHE[cache_key] = ok
        return ok




def _sanitize_types(types: list[str]) -> list[str]:
    out: list[str] = []
    for t in types:
        t = (t or "").strip()
        if t in ALLOWED_PROPERTY_TYPES and t not in out:
            out.append(t)
    return out


def _amenity_on_suffix(am: AmenitiesFilter) -> str:
    """Điều kiện bổ sung cho ON khi INNER JOIN property_amenity."""
    parts: list[str] = []
    d = model_to_dict(am, exclude_none=True)
    for k, v in d.items():
        if v is True:
            parts.append(f"pa.{k} = 1")
    if not parts:
        return ""
    return " AND " + " AND ".join(parts)


def _clone_filters(f: SearchFilters) -> SearchFilters:
    return model_parse(SearchFilters, copy.deepcopy(model_to_dict(f)))


def relax_filters(base: SearchFilters, level: int) -> SearchFilters:
    f = _clone_filters(base)
    if level >= 1:
        f.amenities = AmenitiesFilter()
        f.water_source = None
        f.interior_level = None
        f.frontage_m = None
        f.access_road_m = None
    if level >= 2 and f.price_max_million is not None:
        f.price_max_million = round(float(f.price_max_million) * 1.12, 2)
    if level >= 3:
        f.ward_id = None
    if level >= 4:
        f.property_types = []
    if level >= 5 and f.price_max_million is not None:
        f.price_max_million = round(float(f.price_max_million) * 1.22, 2)
    if level >= 6:
        f.keyword = None
    return f


def _build_query(engine: Engine, f: SearchFilters, for_count: bool) -> tuple[str, dict[str, Any]]:
    settings = get_settings()
    joins: list[str] = [
        "FROM property p",
        "LEFT JOIN city c ON p.city_id = c.cid",
    ]
    has_wards = _schema_has(engine, "table", "wards") and _schema_has(engine, "column", "property.ward_id")
    if has_wards:
        joins.append("LEFT JOIN wards w ON p.ward_id = w.wid")

    has_property_type = _schema_has(engine, "table", "property_type") and _schema_has(engine, "column", "property.type_id")
    if has_property_type:
        joins.append("LEFT JOIN property_type pt ON p.type_id = pt.id")
    has_embedding = _schema_has(engine, "table", "property_embedding")
    if has_embedding:
        joins.append("LEFT JOIN property_embedding pe ON pe.property_id = p.pid")
    params: dict[str, Any] = {}

    am_on = _amenity_on_suffix(f.amenities)
    if am_on:
        joins.append("INNER JOIN property_amenity pa ON pa.property_id = p.pid" + am_on)

    where_parts = [
        "p.approval_status = 'approved'",
        "LOWER(COALESCE(NULLIF(TRIM(p.status), ''), 'available')) NOT IN ('rented','sold')",
    ]

    if f.stype:
        where_parts.append("LOWER(TRIM(p.stype)) = :stype")
        params["stype"] = f.stype.lower()

    if f.city_id is not None:
        where_parts.append("p.city_id = :city_id")
        params["city_id"] = int(f.city_id)

    if f.ward_id is not None:
        if has_wards:
            where_parts.append("p.ward_id = :ward_id")
            params["ward_id"] = int(f.ward_id)

    if f.exclude_ward_ids and has_wards:
        ex_placeholders = ",".join([f":exw{i}" for i in range(len(f.exclude_ward_ids))])
        where_parts.append(f"(p.ward_id IS NULL OR p.ward_id NOT IN ({ex_placeholders}))")
        for i, wid in enumerate(f.exclude_ward_ids):
            params[f"exw{i}"] = int(wid)

    if f.limit_to_pids:
        placeholders = ",".join([f":lp{i}" for i in range(len(f.limit_to_pids))])
        where_parts.append(f"p.pid IN ({placeholders})")
        for i, pid in enumerate(f.limit_to_pids):
            params[f"lp{i}"] = int(pid)

    types = _sanitize_types(f.property_types)
    if types:
        ors: list[str] = []
        for i, t in enumerate(types):
            if has_property_type:
                ors.append(f"(p.type = :tp{i} OR pt.name = :tp{i})")
            else:
                ors.append(f"(p.type = :tp{i})")
            params[f"tp{i}"] = t
        where_parts.append("(" + " OR ".join(ors) + ")")

    if f.bedrooms_min is not None:
        where_parts.append("CAST(COALESCE(p.bedroom, 0) AS UNSIGNED) >= :bmin")
        params["bmin"] = int(f.bedrooms_min)

    has_size = _schema_has(engine, "column", "property.size")
    if has_size and f.size_min is not None:
        where_parts.append("CAST(COALESCE(p.size, 0) AS UNSIGNED) >= :smin")
        params["smin"] = int(f.size_min)
    if has_size and f.size_max is not None:
        where_parts.append("CAST(COALESCE(p.size, 0) AS UNSIGNED) <= :smax")
        params["smax"] = int(f.size_max)

    if f.price_max_million is not None:
        where_parts.append(f"({PRICE_NUMERIC} AND CAST(p.price AS DECIMAL(20,2)) <= :pmax)")
        params["pmax"] = float(f.price_max_million)

    if f.price_min_million is not None:
        where_parts.append(f"({PRICE_NUMERIC} AND CAST(p.price AS DECIMAL(20,2)) >= :pmin)")
        params["pmin"] = float(f.price_min_million)

    if f.keyword and len(f.keyword.strip()) >= 2:
        keyword_fields = ["p.title LIKE :kw", "p.location LIKE :kw"]
        if has_wards:
            keyword_fields.append("w.wname LIKE :kw")
        keyword_fields.append("c.cname LIKE :kw")
        where_parts.append("(" + " OR ".join(keyword_fields) + ")")
        params["kw"] = "%" + f.keyword.strip() + "%"

    # Nếu có ward_name_hint mà chưa resolve ward_id thì ưu tiên match theo tên ward.
    if has_wards and f.ward_id is None and f.ward_name_hint and len(f.ward_name_hint.strip()) >= 2:
        where_parts.append("w.wname LIKE :ward_hint")
        params["ward_hint"] = "%" + f.ward_name_hint.strip() + "%"

    # --- Extended property attributes (property_amenity table) ---
    has_amenity = _schema_has(engine, "table", "property_amenity")
    if has_amenity:
        if f.water_source:
            if not am_on:
                joins.append("INNER JOIN property_amenity pa ON pa.property_id = p.pid")
                am_on = " joined"
            where_parts.append("pa.water_source = :ws")
            params["ws"] = f.water_source
        if f.interior_level:
            if not am_on:
                joins.append("INNER JOIN property_amenity pa ON pa.property_id = p.pid")
                am_on = " joined"
            where_parts.append("pa.interior_level = :il")
            params["il"] = f.interior_level
        if f.frontage_m is not None:
            if not am_on:
                joins.append("INNER JOIN property_amenity pa ON pa.property_id = p.pid")
                am_on = " joined"
            where_parts.append("pa.frontage_m >= :fm")
            params["fm"] = float(f.frontage_m)
        if f.access_road_m is not None:
            if not am_on:
                joins.append("INNER JOIN property_amenity pa ON pa.property_id = p.pid")
                am_on = " joined"
            where_parts.append("pa.access_road_m >= :arm")
            params["arm"] = float(f.access_road_m)

    where_sql = " WHERE " + " AND ".join(where_parts)

    order_sql = " ORDER BY p.date DESC "
    if f.sort == "price_asc":
        order_sql = (
            " ORDER BY CASE WHEN " + PRICE_NUMERIC + " THEN CAST(p.price AS DECIMAL(20,2)) ELSE 999999999 END ASC, "
            "p.date DESC "
        )
    elif f.sort == "price_desc":
        order_sql = (
            " ORDER BY CASE WHEN " + PRICE_NUMERIC + " THEN CAST(p.price AS DECIMAL(20,2)) ELSE 0 END DESC, "
            "p.date DESC "
        )

    base = "\n".join(joins) + where_sql

    if for_count:
        sql = "SELECT COUNT(DISTINCT p.pid) AS cnt " + base
        return sql, params

    limit = max(settings.max_properties_return * 4, settings.max_ranking_candidates)
    ward_select = "NULL AS ward_name"
    if has_wards:
        ward_select = "w.wname AS ward_name"
    type_name_select = "NULL AS property_type_name"
    if has_property_type:
        type_name_select = "pt.name AS property_type_name"
    embedding_select = "NULL AS embedding_json"
    if has_embedding:
        embedding_select = "pe.embedding_json AS embedding_json"
    sql = (
        "SELECT DISTINCT p.pid, p.title, p.pcontent, p.price, p.stype, p.location, p.type, "
        "p.bedroom, p.bathroom, p.size, p.pimage, p.date, p.view_count, "
        f"p.city_id, p.ward_id, c.cname AS city_name, {ward_select}, {type_name_select}, {embedding_select} "
        + base
        + order_sql
        + f" LIMIT {int(limit)}"
    )
    return sql, params


def _row_dt(row: dict[str, Any]) -> Optional[datetime]:
    value = row.get("date")
    if isinstance(value, datetime):
        return value
    if isinstance(value, str) and value:
        try:
            return datetime.fromisoformat(value.replace(" ", "T"))
        except ValueError:
            return None
    return None


def _price_value(row: dict[str, Any]) -> Optional[float]:
    raw = str(row.get("price") or "").strip().replace(",", ".")
    try:
        return float(raw)
    except ValueError:
        return None


def _rank_rows(rows: list[dict[str, Any]], filters: SearchFilters, query_text: str) -> list[dict[str, Any]]:
    qvec = encode_text(query_text_from_filters(query_text, filters))
    now = datetime.now()
    ranked: list[dict[str, Any]] = []
    active_amenities = [
        k for k, v in model_to_dict(filters.amenities, exclude_none=True).items() if v is True
    ] if filters.amenities else []

    for row in rows:
        score = 0.25
        reasons: list[str] = []

        if filters.city_id is not None and int(row.get("city_id") or 0) == int(filters.city_id):
            score += 0.08
            reasons.append("Đúng tỉnh/thành")
        if filters.ward_id is not None and int(row.get("ward_id") or 0) == int(filters.ward_id):
            score += 0.12
            reasons.append("Đúng phường/xã")
        if filters.property_types and str(row.get("type") or "") in filters.property_types:
            score += 0.1
            reasons.append("Đúng loại BĐS")
        if filters.bedrooms_min is not None and int(row.get("bedroom") or 0) >= int(filters.bedrooms_min):
            score += 0.06
            reasons.append("Đủ số phòng ngủ")
        price = _price_value(row)
        if filters.price_max_million is not None and price is not None and price <= float(filters.price_max_million):
            score += 0.06
            reasons.append("Trong ngân sách")
        if active_amenities:
            score += min(0.08, 0.025 * len(active_amenities))
            reasons.append("Khớp tiện ích")

        semantic_score = 0.0
        pvec = vector_from_json(row.get("embedding_json"))
        if qvec is not None and pvec is not None:
            semantic_score = max(0.0, cosine(qvec, pvec))
            score += semantic_score * 0.45
            if semantic_score >= 0.55:
                reasons.insert(0, "Nội dung gần nghĩa với yêu cầu")

        views = int(row.get("view_count") or 0)
        if views > 0:
            score += min(0.08, math.log1p(views) / 70)
            reasons.append("Tin được quan tâm")

        date_value = _row_dt(row)
        if date_value is not None:
            age_days = max(0, (now - date_value).days)
            freshness = max(0.0, 1.0 - min(age_days, 90) / 90)
            score += freshness * 0.06
            if freshness >= 0.7:
                reasons.append("Tin mới")

        if filters.sort == "price_asc" and price is not None:
            score += max(0.0, 0.08 - min(price, 200.0) / 2500)
        elif filters.sort == "price_desc" and price is not None:
            score += min(0.08, price / 2500)

        row["_semantic_score"] = round(float(semantic_score), 4)
        row["_ranking_score"] = round(float(score), 4)
        row["_matched_reasons"] = list(dict.fromkeys(reasons))[:5]
        ranked.append(row)

    return sorted(ranked, key=lambda r: (r.get("_ranking_score") or 0.0), reverse=True)


def search_with_fallback(
    engine: Engine, filters: SearchFilters, query_text: str = ""
) -> tuple[list[PropertyCard], int, int, Optional[str], list[int]]:
    settings = get_settings()
    base_path = settings.public_base_path.rstrip("/")

    last_note: Optional[str] = None
    for level in range(7):
        f = relax_filters(filters, level) if level else filters
        count_sql, params = _build_query(engine, f, for_count=True)
        sel_sql, params2 = _build_query(engine, f, for_count=False)
        with engine.connect() as conn:
            total = int(conn.execute(text(count_sql), params).scalar() or 0)
            if total == 0:
                continue
            rows = [dict(r) for r in conn.execute(text(sel_sql), params2).mappings().all()]

        rows = _rank_rows(rows, f, query_text) if query_text else rows
        context_pids = [int(r["pid"]) for r in rows][: settings.max_last_pids]

        cards: list[PropertyCard] = []
        for row in rows[: settings.max_properties_return]:
            pid = int(row["pid"])
            pim = str(row["pimage"] or "")
            cards.append(
                PropertyCard(
                    pid=pid,
                    title=str(row["title"] or ""),
                    price_raw=str(row["price"] or ""),
                    stype=str(row["stype"] or ""),
                    location=str(row["location"] or ""),
                    city_name=row.get("city_name"),
                    ward_name=row.get("ward_name"),
                    type=str(row["type"] or ""),
                    bedroom=int(row["bedroom"] or 0),
                    pimage=pim,
                    image_path=f"{base_path}/admin/property/{pim}" if pim else "",
                    detail_path=f"{base_path}/property/detail/{pid}",
                    semantic_score=row.get("_semantic_score"),
                    ranking_score=row.get("_ranking_score"),
                    matched_reasons=row.get("_matched_reasons") or [],
                )
            )
        if level > 0:
            relaxed: list[str] = []
            if level >= 1:
                relaxed.append("tiện ích")
            if level >= 2:
                relaxed.append("giá")
            if level >= 3:
                relaxed.append("khu vực")
            if level >= 4:
                relaxed.append("loại BĐS")
            if level >= 6:
                relaxed.append("từ khóa")
            last_note = f"Không có tin khớp hoàn toàn nên mình đã nới lỏng tiêu chí: {', '.join(relaxed)}."
        return cards, total, level, last_note, context_pids

    return [], 0, 6, "Chưa có tin phù hợp trong dữ liệu hiện tại.", []
