from __future__ import annotations

from typing import Any, Literal, Optional

from pydantic import BaseModel, Field

from app.pyd_compat import model_to_dict


IntentType = Literal["property_search", "clarify", "smalltalk", "unknown"]


class AmenitiesFilter(BaseModel):
    swimming_pool: Optional[bool] = None
    parking: Optional[bool] = None
    gym: Optional[bool] = None
    near_school: Optional[bool] = None
    security: Optional[bool] = None
    near_hospital: Optional[bool] = None
    near_market: Optional[bool] = None
    wifi: Optional[bool] = None
    elevator: Optional[bool] = None
    cctv: Optional[bool] = None


class SearchFilters(BaseModel):
    """Bộ lọc chuẩn — đồng bộ với CHAT-BOT-TIMKIEM/API_CONTRACT.md"""

    stype: Optional[Literal["rent"]] = "rent"
    city_id: Optional[int] = None
    ward_id: Optional[int] = None
    ward_name_hint: Optional[str] = None
    property_types: list[str] = Field(default_factory=list)
    bedrooms_min: Optional[int] = None
    price_min_million: Optional[float] = None
    price_max_million: Optional[float] = None
    size_min: Optional[int] = None
    size_max: Optional[int] = None
    keyword: Optional[str] = None
    amenities: AmenitiesFilter = Field(default_factory=AmenitiesFilter)
    sort: Optional[Literal["relevance", "price_asc", "price_desc"]] = None
    limit_to_pids: list[int] = Field(default_factory=list)
    exclude_ward_ids: list[int] = Field(default_factory=list)

    def model_dump_non_null(self) -> dict[str, Any]:
        d = model_to_dict(self, exclude_none=True)
        if "amenities" in d:
            am = {k: v for k, v in d["amenities"].items() if v is not None}
            if am:
                d["amenities"] = am
            else:
                del d["amenities"]
        return d


class PropertyCard(BaseModel):
    pid: int
    title: str
    price_raw: str
    stype: str
    location: str
    city_name: Optional[str] = None
    ward_name: Optional[str] = None
    type: str
    bedroom: int
    pimage: str
    image_path: str = ""
    detail_path: str
    semantic_score: Optional[float] = None
    ranking_score: Optional[float] = None
    matched_reasons: list[str] = Field(default_factory=list)


class ChatMessageRequest(BaseModel):
    session_id: str = Field(min_length=8, max_length=64)
    user_text: str = Field(min_length=1, max_length=4000)
    user_id: Optional[int] = None
    locale: str = "vi-VN"


class ChatMessageResponse(BaseModel):
    reply_text: str
    intent: IntentType
    filters: SearchFilters
    missing_slots: list[str] = Field(default_factory=list)
    follow_up_questions: list[str] = Field(default_factory=list)
    result_count: int = 0
    fallback_level: int = 0
    fallback_note: Optional[str] = None
    properties: list[PropertyCard] = Field(default_factory=list)
    session_id: str


class ChatResetRequest(BaseModel):
    session_id: str
    user_id: Optional[int] = None


class ChatResetResponse(BaseModel):
    ok: bool
    session_id: str


class SuggestionsResponse(BaseModel):
    suggestions: list[str]


class BehaviorEventRequest(BaseModel):
    event_type: Literal[
        "chat_result_impression",
        "chat_result_click",
        "property_detail_view",
        "favorite_from_chat",
    ]
    property_id: int
    session_id: Optional[str] = None
    user_id: Optional[int] = None
    source: str = "chatbot"
    metadata: dict[str, Any] = Field(default_factory=dict)


class BehaviorEventResponse(BaseModel):
    ok: bool


class RecommendationResponse(BaseModel):
    user_id: Optional[int] = None
    strategy: str
    properties: list[PropertyCard] = Field(default_factory=list)


class LlmExtractPayload(BaseModel):
    """Cấu trúc mong muốn từ LLM (partial)."""

    clear_fields: Optional[list[str]] = None
    stype: Optional[Literal["rent"]] = None
    city_id: Optional[int] = None
    ward_id: Optional[int] = None
    ward_name_hint: Optional[str] = None
    property_types: Optional[list[str]] = None
    bedrooms_min: Optional[int] = None
    price_min_million: Optional[float] = None
    price_max_million: Optional[float] = None
    size_min: Optional[int] = None
    size_max: Optional[int] = None
    keyword: Optional[str] = None
    amenities: Optional[dict[str, bool]] = None
