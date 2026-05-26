from __future__ import annotations

import logging
import math
from functools import lru_cache
from typing import Any, Protocol

from app.config import get_settings

logger = logging.getLogger(__name__)


class EmbeddingModel(Protocol):
    def encode(self, sentences: list[str], normalize_embeddings: bool = True) -> Any:
        ...


AMENITY_CONCEPTS: dict[str, list[str]] = {
    "wifi": [
        "wifi",
        "wi-fi",
        "internet",
        "mạng internet",
        "mạng đầy đủ",
        "mạng mẽo đầy đủ",
        "kết nối mạng ổn định",
        "có mạng sẵn",
    ],
    "parking": [
        "bãi xe",
        "chỗ đậu xe",
        "chỗ để xe",
        "gửi xe",
        "có nơi để xe",
    ],
    "security": [
        "an ninh tốt",
        "bảo vệ",
        "khu an toàn",
        "bảo vệ an ninh",
    ],
    "gym": [
        "gym",
        "phòng gym",
        "phòng tập gym",
        "fitness",
    ],
    "swimming_pool": [
        "hồ bơi",
        "bể bơi",
        "pool",
    ],
    "near_school": [
        "gần trường",
        "gần trường học",
        "near school",
    ],
    "near_hospital": [
        "gần bệnh viện",
        "gần cơ sở y tế",
        "near hospital",
    ],
    "near_market": [
        "gần chợ",
        "gần siêu thị",
        "gần nơi mua sắm",
    ],
    "elevator": [
        "thang máy",
        "có thang máy",
        "elevator",
    ],
    "cctv": [
        "camera",
        "camera an ninh",
        "cctv",
    ],
}


def _to_vector(value: Any) -> list[float]:
    if hasattr(value, "tolist"):
        value = value.tolist()
    return [float(x) for x in value]


def _cosine(a: list[float], b: list[float]) -> float:
    dot = sum(x * y for x, y in zip(a, b))
    norm_a = math.sqrt(sum(x * x for x in a))
    norm_b = math.sqrt(sum(y * y for y in b))
    if norm_a == 0 or norm_b == 0:
        return 0.0
    return dot / (norm_a * norm_b)


@lru_cache(maxsize=1)
def _load_model() -> EmbeddingModel | None:
    settings = get_settings()
    if not settings.embedding_enabled:
        return None
    try:
        from sentence_transformers import SentenceTransformer

        return SentenceTransformer(settings.embedding_model)
    except Exception as exc:
        logger.warning("semantic embedding disabled: could not load model: %s", exc)
        return None


@lru_cache(maxsize=1)
def _concept_vectors() -> dict[str, list[list[float]]]:
    model = _load_model()
    if model is None:
        return {}

    vectors: dict[str, list[list[float]]] = {}
    for name, phrases in AMENITY_CONCEPTS.items():
        encoded = model.encode(phrases, normalize_embeddings=True)
        vectors[name] = [_to_vector(item) for item in encoded]
    return vectors


def extract_semantic_filters(normalized_text: str) -> dict[str, Any]:
    settings = get_settings()
    if not settings.embedding_enabled:
        return {}

    text = (normalized_text or "").strip()
    if not text:
        return {}

    model = _load_model()
    concepts = _concept_vectors()
    if model is None or not concepts:
        return {}

    try:
        user_vec = _to_vector(model.encode([text], normalize_embeddings=True)[0])
    except Exception as exc:
        logger.warning("semantic embedding disabled for request: %s", exc)
        return {}

    amenities: dict[str, bool] = {}
    for name, vectors in concepts.items():
        best = max((_cosine(user_vec, vec) for vec in vectors), default=0.0)
        if best >= settings.embedding_threshold:
            amenities[name] = True

    if amenities:
        return {"amenities": amenities}
    return {}
