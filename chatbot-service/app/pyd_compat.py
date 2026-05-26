"""Tương thích Pydantic v1 / v2 (một số môi trường còn pydantic 1.x)."""

from __future__ import annotations

from typing import Any, Type, TypeVar

from pydantic import BaseModel

T = TypeVar("T", bound=BaseModel)


def model_to_dict(model: BaseModel, **kwargs: Any) -> dict[str, Any]:
    if hasattr(model, "model_dump"):
        return model.model_dump(**kwargs)
    # Pydantic v1
    allowed = {k: v for k, v in kwargs.items() if k in ("exclude_none", "exclude_unset")}
    return model.dict(**allowed)


def model_parse(cls: Type[T], data: Any) -> T:
    if hasattr(cls, "model_validate"):
        return cls.model_validate(data)
    return cls.parse_obj(data)


def model_with_update(model: T, **updates: Any) -> T:
    """Pydantic v2 model_copy(update=...) hoặc v1 copy(update=...)."""
    if hasattr(model, "model_copy"):
        return model.model_copy(update=updates)
    return model.copy(update=updates)
