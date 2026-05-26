from __future__ import annotations

import json
import math
import re
import unicodedata
from datetime import datetime
from hashlib import blake2b
from typing import Any, Iterable, Optional

from sqlalchemy import text
from sqlalchemy.engine import Engine

from app.config import get_settings
from app.pyd_compat import model_to_dict
from app.schemas import SearchFilters
from app.semantic_extract import _load_model


EMBEDDING_TABLE_SQL = """
CREATE TABLE IF NOT EXISTS property_embedding (
  property_id INT NOT NULL PRIMARY KEY,
  embedding_json LONGTEXT NOT NULL,
  normalized_text LONGTEXT NOT NULL,
  feature_json LONGTEXT NULL,
  model_name VARCHAR(180) NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_property_embedding_property
    FOREIGN KEY (property_id) REFERENCES property(pid)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
"""


def ensure_embedding_table(engine: Engine) -> None:
    with engine.begin() as conn:
        conn.execute(text(EMBEDDING_TABLE_SQL))


def normalize_text(value: str) -> str:
    value = unicodedata.normalize("NFKC", value or "").lower()
    value = re.sub(r"<[^>]+>", " ", value)
    value = re.sub(r"[^\w\sÀ-ỹà-ỹ.,:/-]", " ", value, flags=re.UNICODE)
    return re.sub(r"\s+", " ", value).strip()


def _hash_embedding(value: str, dimensions: int = 384) -> list[float]:
    """
    Lightweight deterministic fallback when sentence-transformers is unavailable.
    It is weaker than a real multilingual embedding model, but keeps the AI search
    pipeline usable for local demo and tests.
    """
    text_value = normalize_text(value)
    tokens = re.findall(r"[\wÀ-ỹà-ỹ]+", text_value, flags=re.UNICODE)
    vec = [0.0] * dimensions
    if not tokens:
        return vec

    grams: list[str] = []
    for token in tokens:
        grams.append(token)
        if len(token) >= 4:
            grams.extend(token[i : i + 4] for i in range(0, len(token) - 3))

    for gram in grams:
        digest = blake2b(gram.encode("utf-8"), digest_size=8).digest()
        raw = int.from_bytes(digest, "big", signed=False)
        idx = raw % dimensions
        sign = 1.0 if ((raw >> 8) & 1) else -1.0
        vec[idx] += sign

    norm = math.sqrt(sum(x * x for x in vec))
    if norm == 0:
        return vec
    return [x / norm for x in vec]


def encode_text(value: str) -> Optional[list[float]]:
    settings = get_settings()
    if not settings.embedding_enabled:
        return None
    model = _load_model()
    if model is None:
        return _hash_embedding(value)
    try:
        vec = model.encode([value], normalize_embeddings=True)[0]
    except Exception:
        return _hash_embedding(value)
    if hasattr(vec, "tolist"):
        vec = vec.tolist()
    return [float(x) for x in vec]


def cosine(a: Iterable[float], b: Iterable[float]) -> float:
    av = list(a)
    bv = list(b)
    if not av or not bv or len(av) != len(bv):
        return 0.0
    dot = sum(x * y for x, y in zip(av, bv))
    na = math.sqrt(sum(x * x for x in av))
    nb = math.sqrt(sum(y * y for y in bv))
    if na == 0 or nb == 0:
        return 0.0
    return dot / (na * nb)


def vector_from_json(value: Any) -> Optional[list[float]]:
    if value is None:
        return None
    try:
        data = json.loads(value) if isinstance(value, str) else value
    except (TypeError, json.JSONDecodeError):
        return None
    if not isinstance(data, list):
        return None
    try:
        return [float(x) for x in data]
    except (TypeError, ValueError):
        return None


def query_text_from_filters(user_text: str, filters: SearchFilters) -> str:
    parts = [user_text or ""]
    if filters.keyword:
        parts.append(filters.keyword)
    if filters.ward_name_hint:
        parts.append(filters.ward_name_hint)
    if filters.property_types:
        parts.extend(filters.property_types)
    am = model_to_dict(filters.amenities, exclude_none=True) if filters.amenities else {}
    parts.extend(k.replace("_", " ") for k, v in am.items() if v is True)
    if filters.bedrooms_min:
        parts.append(f"{filters.bedrooms_min} phong ngu")
    if filters.price_max_million:
        parts.append(f"duoi {filters.price_max_million} trieu")
    return normalize_text(" ".join(parts))


def property_text_from_row(row: dict[str, Any]) -> str:
    parts = [
        row.get("title"),
        row.get("pcontent"),
        row.get("type"),
        row.get("property_type_name"),
        row.get("location"),
        row.get("ward_name"),
        row.get("city_name"),
        f"{row.get('bedroom') or 0} phong ngu",
        f"{row.get('bathroom') or 0} phong tam",
        f"{row.get('size') or 0} m2",
        f"{row.get('price') or ''} trieu",
    ]
    for key in (
        "swimming_pool",
        "parking",
        "gym",
        "near_school",
        "security",
        "near_hospital",
        "near_market",
        "wifi",
        "elevator",
        "cctv",
    ):
        if row.get(key):
            parts.append(key.replace("_", " "))
    return normalize_text(" ".join(str(p or "") for p in parts))


def feature_json_from_row(row: dict[str, Any]) -> dict[str, Any]:
    return {
        "city_id": row.get("city_id"),
        "ward_id": row.get("ward_id"),
        "type": row.get("type"),
        "bedroom": row.get("bedroom"),
        "bathroom": row.get("bathroom"),
        "size": row.get("size"),
        "price": row.get("price"),
        "view_count": row.get("view_count"),
    }


def upsert_property_embedding(engine: Engine, row: dict[str, Any], model_name: Optional[str] = None) -> bool:
    normalized = property_text_from_row(row)
    vec = encode_text(normalized)
    if vec is None:
        return False
    ensure_embedding_table(engine)
    settings = get_settings()
    with engine.begin() as conn:
        conn.execute(
            text(
                """
                INSERT INTO property_embedding
                  (property_id, embedding_json, normalized_text, feature_json, model_name, updated_at)
                VALUES (:pid, :embedding, :txt, :feature, :model, :updated_at)
                ON DUPLICATE KEY UPDATE
                  embedding_json = VALUES(embedding_json),
                  normalized_text = VALUES(normalized_text),
                  feature_json = VALUES(feature_json),
                  model_name = VALUES(model_name),
                  updated_at = VALUES(updated_at)
                """
            ),
            {
                "pid": int(row["pid"]),
                "embedding": json.dumps(vec),
                "txt": normalized,
                "feature": json.dumps(feature_json_from_row(row), ensure_ascii=False),
                "model": model_name or settings.embedding_model,
                "updated_at": datetime.now(),
            },
        )
    return True
