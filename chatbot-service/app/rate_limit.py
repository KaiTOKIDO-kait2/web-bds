from __future__ import annotations

import time
from collections import deque
from dataclasses import dataclass


@dataclass(frozen=True)
class RateLimitConfig:
    # simple sliding-window limiter
    max_requests: int = 12
    window_seconds: int = 30


_BUCKETS: dict[str, deque[float]] = {}
_CFG = RateLimitConfig()


def check_rate_limit(key: str) -> bool:
    """
    Returns True if allowed, False if rate-limited.
    In-memory only (per-process) — đủ cho MVP / đồ án.
    """
    now = time.time()
    q = _BUCKETS.get(key)
    if q is None:
        q = deque()
        _BUCKETS[key] = q

    # drop old
    cutoff = now - _CFG.window_seconds
    while q and q[0] < cutoff:
        q.popleft()

    if len(q) >= _CFG.max_requests:
        return False

    q.append(now)
    return True

