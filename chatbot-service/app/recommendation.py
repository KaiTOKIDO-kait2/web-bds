from __future__ import annotations

from typing import Any, Optional

from sqlalchemy import text
from sqlalchemy.engine import Engine

from app.ai_vectors import cosine, vector_from_json
from app.config import get_settings
from app.property_search import _schema_has
from app.schemas import PropertyCard


def _card_from_row(row: dict[str, Any]) -> PropertyCard:
    base_path = get_settings().public_base_path.rstrip("/")
    pim = str(row.get("pimage") or "")
    return PropertyCard(
        pid=int(row["pid"]),
        title=str(row.get("title") or ""),
        price_raw=str(row.get("price") or ""),
        stype=str(row.get("stype") or ""),
        location=str(row.get("location") or ""),
        city_name=row.get("city_name"),
        ward_name=row.get("ward_name"),
        type=str(row.get("type") or ""),
        bedroom=int(row.get("bedroom") or 0),
        pimage=pim,
        image_path=f"{base_path}/admin/property/{pim}" if pim else "",
        detail_path=f"{base_path}/property/detail/{int(row['pid'])}",
        semantic_score=row.get("_semantic_score"),
        ranking_score=row.get("_ranking_score"),
        matched_reasons=row.get("_matched_reasons") or [],
    )


def _average(vectors: list[list[float]]) -> Optional[list[float]]:
    if not vectors:
        return None
    size = len(vectors[0])
    if size == 0:
        return None
    out = [0.0] * size
    count = 0
    for vec in vectors:
        if len(vec) != size:
            continue
        count += 1
        for i, value in enumerate(vec):
            out[i] += value
    if count == 0:
        return None
    return [v / count for v in out]


def _load_user_profile(engine: Engine, user_id: int) -> tuple[Optional[list[float]], set[int]]:
    if not _schema_has(engine, "table", "property_embedding"):
        return None, set()

    sources: list[tuple[str, dict[str, Any]]] = [
        (
            """
            SELECT pe.property_id, pe.embedding_json, 4.0 AS weight
            FROM property_favorite pf
            INNER JOIN property_embedding pe ON pe.property_id = pf.pid
            WHERE pf.uid = :uid
            ORDER BY pf.created_at DESC
            LIMIT 30
            """,
            {"uid": user_id},
        ),
        (
            """
            SELECT pe.property_id, pe.embedding_json, 5.0 AS weight
            FROM property_inquiry pi
            INNER JOIN property_embedding pe ON pe.property_id = pi.property_id
            WHERE pi.inquirer_uid = :uid
            ORDER BY pi.created_at DESC
            LIMIT 30
            """,
            {"uid": user_id},
        ),
        (
            """
            SELECT pe.property_id, pe.embedding_json, 3.0 AS weight
            FROM property_owner_call_click pc
            INNER JOIN property_embedding pe ON pe.property_id = pc.property_id
            WHERE pc.caller_uid = :uid
            ORDER BY pc.clicked_at DESC
            LIMIT 30
            """,
            {"uid": user_id},
        ),
    ]
    if _schema_has(engine, "table", "chatbot_event"):
        sources.append(
            (
                """
                SELECT pe.property_id, pe.embedding_json,
                  CASE ce.event_type
                    WHEN 'chat_result_click' THEN 3.0
                    WHEN 'favorite_from_chat' THEN 4.0
                    WHEN 'property_detail_view' THEN 2.0
                    ELSE 0.6
                  END AS weight
                FROM chatbot_event ce
                INNER JOIN property_embedding pe ON pe.property_id = ce.property_id
                WHERE ce.user_uid = :uid
                ORDER BY ce.created_at DESC
                LIMIT 60
                """,
                {"uid": user_id},
            )
        )

    vectors: list[list[float]] = []
    interacted: set[int] = set()
    with engine.connect() as conn:
        for sql, params in sources:
            for row in conn.execute(text(sql), params).mappings().all():
                pid = int(row["property_id"])
                interacted.add(pid)
                vec = vector_from_json(row.get("embedding_json"))
                if vec is None:
                    continue
                weight = max(0.1, float(row.get("weight") or 1.0))
                vectors.extend([vec] * max(1, int(round(weight))))

    return _average(vectors), interacted


def _candidate_rows(engine: Engine, exclude_ids: set[int], limit: int) -> list[dict[str, Any]]:
    has_wards = _schema_has(engine, "table", "wards") and _schema_has(engine, "column", "property.ward_id")
    has_embedding = _schema_has(engine, "table", "property_embedding")
    joins = ["FROM property p", "LEFT JOIN city c ON p.city_id = c.cid"]
    if has_wards:
        joins.append("LEFT JOIN wards w ON p.ward_id = w.wid")
    if has_embedding:
        joins.append("LEFT JOIN property_embedding pe ON pe.property_id = p.pid")

    ward_select = "NULL AS ward_name"
    if has_wards:
        ward_select = "w.wname AS ward_name"
    embedding_select = "NULL AS embedding_json"
    if has_embedding:
        embedding_select = "pe.embedding_json AS embedding_json"

    params: dict[str, Any] = {}
    where = [
        "p.approval_status = 'approved'",
        "LOWER(COALESCE(NULLIF(TRIM(p.status), ''), 'available')) NOT IN ('rented','sold')",
        "LOWER(TRIM(p.stype)) = 'rent'",
    ]
    if exclude_ids:
        placeholders = []
        for i, pid in enumerate(sorted(exclude_ids)):
            key = f"ex{i}"
            placeholders.append(f":{key}")
            params[key] = int(pid)
        where.append("p.pid NOT IN (" + ",".join(placeholders) + ")")

    sql = (
        "SELECT p.pid, p.title, p.price, p.stype, p.location, p.type, p.bedroom, p.pimage, "
        f"p.view_count, p.date, c.cname AS city_name, {ward_select}, {embedding_select} "
        + "\n".join(joins)
        + " WHERE "
        + " AND ".join(where)
        + " ORDER BY COALESCE(p.view_count, 0) DESC, p.date DESC"
        + f" LIMIT {int(limit)}"
    )
    with engine.connect() as conn:
        return [dict(r) for r in conn.execute(text(sql), params).mappings().all()]


def recommend_properties(engine: Engine, user_id: Optional[int], limit: Optional[int] = None) -> tuple[str, list[PropertyCard]]:
    settings = get_settings()
    limit = int(limit or settings.recommendation_return)

    profile: Optional[list[float]] = None
    interacted: set[int] = set()
    if user_id and user_id > 0:
        profile, interacted = _load_user_profile(engine, int(user_id))

    rows = _candidate_rows(engine, interacted, max(limit * 6, 40))
    if profile is None:
        cards = []
        for row in rows[:limit]:
            row["_ranking_score"] = 0.0
            row["_matched_reasons"] = ["Tin phổ biến", "Tin mới"]
            cards.append(_card_from_row(row))
        return "popular_recent", cards

    ranked: list[dict[str, Any]] = []
    for row in rows:
        vec = vector_from_json(row.get("embedding_json"))
        semantic = cosine(profile, vec) if vec is not None else 0.0
        views = min(0.08, float(row.get("view_count") or 0) / 1000.0)
        score = semantic * 0.86 + views
        row["_semantic_score"] = round(semantic, 4)
        row["_ranking_score"] = round(score, 4)
        row["_matched_reasons"] = ["Gần với tin bạn đã quan tâm"]
        if views > 0:
            row["_matched_reasons"].append("Tin được quan tâm")
        ranked.append(row)

    ranked.sort(key=lambda r: (r.get("_ranking_score") or 0.0), reverse=True)
    return "personalized_content", [_card_from_row(row) for row in ranked[:limit]]
