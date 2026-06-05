from __future__ import annotations

import logging
import math
from functools import lru_cache
from typing import Any

from app.config import get_settings

logger = logging.getLogger(__name__)


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


def _cosine(a: list[float], b: list[float]) -> float:
    if not a or not b or len(a) != len(b):
        return 0.0
    dot = sum(x * y for x, y in zip(a, b))
    norm_a = math.sqrt(sum(x * x for x in a))
    norm_b = math.sqrt(sum(y * y for y in b))
    if norm_a == 0 or norm_b == 0:
        return 0.0
    return dot / (norm_a * norm_b)


# Giữ lại hàm _load_model cho backward compatibility (ai_vectors.py import nó)
@lru_cache(maxsize=1)
def _load_model():
    return None


@lru_cache(maxsize=1)
def _concept_vectors() -> dict[str, list[list[float]]]:
    """Tạo concept vectors cho amenities bằng Voyage API (gọi 1 lần duy nhất, cached)."""
    from app.ai_vectors import _voyage_encode

    # Gom tất cả phrases thành 1 batch
    all_phrases: list[str] = []
    mapping: list[tuple[str, int]] = []  # (amenity_name, phrase_count)
    for name, phrases in AMENITY_CONCEPTS.items():
        mapping.append((name, len(phrases)))
        all_phrases.extend(phrases)

    result = _voyage_encode(all_phrases)
    if result is None or len(result) != len(all_phrases):
        logger.warning("concept vectors: Voyage API unavailable, semantic extract disabled")
        return {}

    vectors: dict[str, list[list[float]]] = {}
    idx = 0
    for name, count in mapping:
        vectors[name] = [[float(x) for x in result[idx + i]] for i in range(count)]
        idx += count
    logger.info("concept vectors loaded: %d amenities via Voyage API", len(vectors))
    return vectors


def extract_semantic_filters(normalized_text: str) -> dict[str, Any]:
    """Amenity detection bằng cosine similarity không đủ chính xác (false positive cao).
    Embedding chỉ dùng cho property ranking. Amenity detection dùng rules + LLM."""
    return {}

