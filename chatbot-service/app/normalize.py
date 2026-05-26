from __future__ import annotations

import re
import unicodedata


def strip_accents(s: str) -> str:
    s = unicodedata.normalize("NFD", s)
    return "".join(c for c in s if unicodedata.category(c) != "Mn")


def normalize_user_text(text: str) -> str:
    t = text.strip()
    if not t:
        return t
    t = re.sub(r"\s+", " ", t)
    low = t.lower()

    # Viết tắt phổ biến
    replacements = [
        (r"\b2pn\b", "2 phòng ngủ"),
        (r"\b1pn\b", "1 phòng ngủ"),
        (r"\b3pn\b", "3 phòng ngủ"),
        (r"\b4pn\b", "4 phòng ngủ"),
        (r"\b(\d+)\s*pn\b", r"\1 phòng ngủ"),
        (r"\bqhcm\b", "hồ chí minh"),
        (r"\bsg\b", "sài gòn"),
        (r"\btphcm\b", "thành phố hồ chí minh"),
        (r"\bhcm\b", "hồ chí minh"),
        (r"\b(\d+)\s*tr\b", r"\1 triệu"),
        (r"\b(\d+)\s*củ\b", r"\1 triệu"),
        (r"\b(\d+)\s*cu\b", r"\1 triệu"),
        (r"\bstudio\b", "studio"),
    ]
    for pat, repl in replacements:
        low = re.sub(pat, repl, low, flags=re.IGNORECASE)

    return low[:4000]
