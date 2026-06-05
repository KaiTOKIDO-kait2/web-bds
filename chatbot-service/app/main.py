from __future__ import annotations

from typing import Annotated, Optional

from fastapi import FastAPI, Header, HTTPException, Query, Request
from fastapi.middleware.cors import CORSMiddleware

from app.behavior_events import log_behavior_event
from app.chat_service import handle_chat_message, handle_reset, handle_suggestions
from app.config import get_settings
from app.db import get_engine, session_scope
from app.rate_limit import check_rate_limit
from app.recommendation import recommend_properties
from app.semantic_extract import _concept_vectors
from app.schemas import (
    BehaviorEventRequest,
    BehaviorEventResponse,
    ChatMessageRequest,
    ChatMessageResponse,
    ChatResetRequest,
    ChatResetResponse,
    RecommendationResponse,
    SuggestionsResponse,
)

app = FastAPI(title="Real Estate Chatbot Search", version="0.1.0")

_cfg = get_settings()
_origins = [o.strip() for o in _cfg.cors_origins.split(",") if o.strip()]
if not _origins or _origins == ["*"]:
    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )
else:
    app.add_middleware(
        CORSMiddleware,
        allow_origins=_origins,
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )


@app.on_event("startup")
def warmup_embeddings() -> None:
    """Pre-load concept vectors on startup to avoid first-request latency."""
    settings = get_settings()
    if not settings.embedding_enabled:
        return
    # Pre-load amenity concept vectors via Voyage API (cached by lru_cache)
    _concept_vectors()


def _check_internal_secret(x_internal_secret: Optional[str]) -> None:
    expected = (get_settings().internal_secret or "").strip()
    if not expected:
        return
    if (x_internal_secret or "").strip() != expected:
        raise HTTPException(status_code=401, detail="Invalid internal secret")


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@app.post("/v1/chat/message", response_model=ChatMessageResponse)
def post_chat_message(
    body: ChatMessageRequest,
    request: Request,
    x_internal_secret: Annotated[Optional[str], Header(alias="X-Internal-Secret")] = None,
) -> ChatMessageResponse:
    _check_internal_secret(x_internal_secret)
    # Basic anti-spam to protect LLM cost (per-process, MVP)
    client_ip = (request.client.host if request.client else "") or "unknown"
    rl_key = f"{client_ip}:{body.session_id.strip()}"
    if not check_rate_limit(rl_key):
        raise HTTPException(status_code=429, detail="Rate limit exceeded")
    return handle_chat_message(
        public_session_id=body.session_id.strip(),
        user_text=body.user_text,
        user_uid=body.user_id,
    )


@app.post("/v1/chat/reset", response_model=ChatResetResponse)
def post_chat_reset(
    body: ChatResetRequest,
    x_internal_secret: Annotated[Optional[str], Header(alias="X-Internal-Secret")] = None,
) -> ChatResetResponse:
    _check_internal_secret(x_internal_secret)
    handle_reset(body.session_id.strip(), body.user_id)
    return ChatResetResponse(ok=True, session_id=body.session_id.strip())


@app.get("/v1/chat/suggestions", response_model=SuggestionsResponse)
def get_suggestions(
    session_id: str = Query(..., min_length=8),
    user_id: Optional[int] = None,
    x_internal_secret: Annotated[Optional[str], Header(alias="X-Internal-Secret")] = None,
) -> SuggestionsResponse:
    _check_internal_secret(x_internal_secret)
    return handle_suggestions(session_id.strip(), user_id)


@app.post("/v1/events", response_model=BehaviorEventResponse)
def post_behavior_event(
    body: BehaviorEventRequest,
    x_internal_secret: Annotated[Optional[str], Header(alias="X-Internal-Secret")] = None,
) -> BehaviorEventResponse:
    _check_internal_secret(x_internal_secret)
    with session_scope() as db:
        log_behavior_event(
            db,
            event_type=body.event_type,
            property_id=body.property_id,
            user_uid=body.user_id,
            public_session_id=(body.session_id or "").strip() or None,
            source=body.source,
            metadata=body.metadata,
        )
    return BehaviorEventResponse(ok=True)


@app.get("/v1/recommendations", response_model=RecommendationResponse)
def get_recommendations(
    user_id: Optional[int] = None,
    limit: int = Query(8, ge=1, le=30),
    x_internal_secret: Annotated[Optional[str], Header(alias="X-Internal-Secret")] = None,
) -> RecommendationResponse:
    _check_internal_secret(x_internal_secret)
    strategy, cards = recommend_properties(get_engine(), user_id, limit=limit)
    return RecommendationResponse(user_id=user_id, strategy=strategy, properties=cards)
