from __future__ import annotations

import argparse
from typing import Any

from sqlalchemy import text
from sqlalchemy.engine import Engine

from app.ai_vectors import ensure_embedding_table, upsert_property_embedding
from app.db import get_engine
from app.property_search import _schema_has


def _property_rows(engine: Engine, limit: int | None = None) -> list[dict[str, Any]]:
    has_wards = _schema_has(engine, "table", "wards") and _schema_has(engine, "column", "property.ward_id")
    has_property_type = _schema_has(engine, "table", "property_type") and _schema_has(engine, "column", "property.type_id")
    has_amenity = _schema_has(engine, "table", "property_amenity")

    joins = ["FROM property p", "LEFT JOIN city c ON p.city_id = c.cid"]
    if has_wards:
        joins.append("LEFT JOIN wards w ON p.ward_id = w.wid")
    if has_property_type:
        joins.append("LEFT JOIN property_type pt ON p.type_id = pt.id")
    if has_amenity:
        joins.append("LEFT JOIN property_amenity pa ON pa.property_id = p.pid")

    ward_select = "NULL AS ward_name"
    if has_wards:
        ward_select = "w.wname AS ward_name"
    type_select = "NULL AS property_type_name"
    if has_property_type:
        type_select = "pt.name AS property_type_name"
    amenity_select = "0 AS swimming_pool, 0 AS parking, 0 AS gym, 0 AS near_school, 0 AS security, 0 AS near_hospital, 0 AS near_market, 0 AS wifi, 0 AS elevator, 0 AS cctv"
    if has_amenity:
        amenity_select = (
            "COALESCE(pa.swimming_pool, 0) AS swimming_pool, COALESCE(pa.parking, 0) AS parking, "
            "COALESCE(pa.gym, 0) AS gym, COALESCE(pa.near_school, 0) AS near_school, "
            "COALESCE(pa.security, 0) AS security, COALESCE(pa.near_hospital, 0) AS near_hospital, "
            "COALESCE(pa.near_market, 0) AS near_market, COALESCE(pa.wifi, 0) AS wifi, "
            "COALESCE(pa.elevator, 0) AS elevator, COALESCE(pa.cctv, 0) AS cctv"
        )

    limit_sql = f" LIMIT {int(limit)}" if limit else ""
    sql = (
        "SELECT p.pid, p.title, p.pcontent, p.price, p.stype, p.location, p.type, p.bedroom, "
        "p.bathroom, p.size, p.city_id, p.ward_id, p.view_count, "
        f"c.cname AS city_name, {ward_select}, {type_select}, {amenity_select} "
        + "\n".join(joins)
        + " WHERE p.approval_status = 'approved' AND LOWER(TRIM(p.stype)) = 'rent'"
        + " ORDER BY p.pid ASC"
        + limit_sql
    )
    with engine.connect() as conn:
        return [dict(r) for r in conn.execute(text(sql)).mappings().all()]


def rebuild_property_embeddings(limit: int | None = None) -> tuple[int, int]:
    import time
    from app.ai_vectors import property_text_from_row, _voyage_encode, _hash_embedding

    engine = get_engine()
    ensure_embedding_table(engine)
    rows = _property_rows(engine, limit=limit)

    BATCH_SIZE = 10
    ok = 0
    for i in range(0, len(rows), BATCH_SIZE):
        batch = rows[i : i + BATCH_SIZE]
        texts = [property_text_from_row(r) for r in batch]

        # Gọi Voyage API 1 lần cho cả batch
        vectors = _voyage_encode(texts)
        if vectors and len(vectors) == len(batch):
            for row, vec, txt in zip(batch, vectors, texts):
                vec_list = [float(x) for x in vec]
                _upsert_vec(engine, int(row["pid"]), vec_list, txt)
                ok += 1
        else:
            # Fallback: hash embedding từng tin
            for row in batch:
                if upsert_property_embedding(engine, row):
                    ok += 1

        # Delay giữa các batch để tránh rate limit
        if i + BATCH_SIZE < len(rows):
            time.sleep(2.0)

    return ok, len(rows)


def _upsert_vec(engine: Engine, pid: int, vec: list[float], norm_text: str = "") -> None:
    import json
    from app.config import get_settings
    settings = get_settings()
    with engine.begin() as conn:
        conn.execute(
            text(
                "INSERT INTO property_embedding (property_id, embedding_json, normalized_text, model_name) "
                "VALUES (:pid, :ej, :nt, :mn) "
                "ON DUPLICATE KEY UPDATE embedding_json = VALUES(embedding_json), "
                "normalized_text = VALUES(normalized_text), model_name = VALUES(model_name)"
            ),
            {"pid": pid, "ej": json.dumps(vec), "nt": norm_text, "mn": settings.voyage_model},
        )


def main() -> None:
    parser = argparse.ArgumentParser(description="Build property embeddings for hybrid AI search.")
    parser.add_argument("--limit", type=int, default=None, help="Optional max number of properties to process.")
    args = parser.parse_args()
    ok, total = rebuild_property_embeddings(limit=args.limit)
    print(f"property embeddings rebuilt: {ok}/{total}")


if __name__ == "__main__":
    main()
