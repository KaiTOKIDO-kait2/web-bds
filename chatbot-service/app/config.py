from __future__ import annotations

import os

try:
    from dotenv import load_dotenv

    load_dotenv()
except ImportError:
    pass
from dataclasses import dataclass
from functools import lru_cache


def _env_str(key: str, default: str) -> str:
    v = os.getenv(key)
    return default if v is None or v.strip() == "" else v


def _env_int(key: str, default: int) -> int:
    v = os.getenv(key)
    if v is None or v.strip() == "":
        return default
    try:
        return int(v)
    except ValueError:
        return default


def _env_bool(key: str, default: bool) -> bool:
    v = os.getenv(key)
    if v is None or v.strip() == "":
        return default
    return v.strip().lower() in {"1", "true", "yes", "on"}


def _env_float(key: str, default: float) -> float:
    v = os.getenv(key)
    if v is None or v.strip() == "":
        return default
    try:
        return float(v)
    except ValueError:
        return default


@dataclass(frozen=True)
class Settings:
    mysql_host: str
    mysql_port: int
    mysql_user: str
    mysql_password: str
    mysql_database: str
    public_base_path: str
    internal_secret: str
    deepseek_api_key: str
    deepseek_api_url: str
    deepseek_model: str
    cors_origins: str
    max_user_text_len: int
    max_properties_return: int
    max_last_pids: int
    max_ranking_candidates: int
    recommendation_return: int
    embedding_enabled: bool
    embedding_model: str
    embedding_threshold: float
    voyage_api_key: str
    voyage_api_url: str
    voyage_model: str


@lru_cache
def get_settings() -> Settings:
    return Settings(
        mysql_host=_env_str("MYSQL_HOST", "127.0.0.1"),
        mysql_port=_env_int("MYSQL_PORT", 3306),
        mysql_user=_env_str("MYSQL_USER", "root"),
        mysql_password=_env_str("MYSQL_PASSWORD", ""),
        mysql_database=_env_str("MYSQL_DATABASE", "realestatephp_new"),
        public_base_path=_env_str("PUBLIC_BASE_PATH", ""),
        internal_secret=_env_str("INTERNAL_SECRET", ""),
        deepseek_api_key=_env_str("DEEPSEEK_API_KEY", ""),
        deepseek_api_url=_env_str("DEEPSEEK_API_URL", "https://api.deepseek.com/v1/chat/completions"),
        deepseek_model=_env_str("DEEPSEEK_MODEL", "deepseek-chat"),
        cors_origins=_env_str("CORS_ORIGINS", "*"),
        max_user_text_len=_env_int("MAX_USER_TEXT_LEN", 2000),
        max_properties_return=_env_int("MAX_PROPERTIES_RETURN", 8),
        max_last_pids=_env_int("MAX_LAST_PIDS", 50),
        max_ranking_candidates=_env_int("MAX_RANKING_CANDIDATES", 80),
        recommendation_return=_env_int("RECOMMENDATION_RETURN", 8),
        embedding_enabled=_env_bool("EMBEDDING_ENABLED", False),
        embedding_model=_env_str("EMBEDDING_MODEL", "voyage-4"),
        embedding_threshold=_env_float("EMBEDDING_THRESHOLD", 0.50),
        voyage_api_key=_env_str("VOYAGE_API_KEY", ""),
        voyage_api_url=_env_str("VOYAGE_API_URL", "https://api.voyageai.com/v1/embeddings"),
        voyage_model=_env_str("VOYAGE_MODEL", "voyage-4"),
    )


def database_url() -> str:
    s = get_settings()
    pwd = s.mysql_password
    # pymysql hỗ trợ mật khẩu rỗng
    return (
        f"mysql+pymysql://{s.mysql_user}:{pwd}"
        f"@{s.mysql_host}:{s.mysql_port}/{s.mysql_database}?charset=utf8mb4"
    )
