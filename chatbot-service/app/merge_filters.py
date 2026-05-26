from __future__ import annotations

from typing import Any

from app.pyd_compat import model_parse, model_to_dict
from app.schemas import AmenitiesFilter, SearchFilters


_CLEARABLE_FIELDS = {
    "city_id",
    "ward_id",
    "ward_name_hint",
    "property_types",
    "bedrooms_min",
    "price_min_million",
    "price_max_million",
    "size_min",
    "size_max",
    "keyword",
    "amenities",
    "sort",
    "limit_to_pids",
    "exclude_ward_ids",
}


def _clear_field(data: dict[str, Any], field: str) -> None:
    if field not in _CLEARABLE_FIELDS:
        return
    if field in {"property_types", "limit_to_pids", "exclude_ward_ids"}:
        data[field] = []
    elif field == "amenities":
        data[field] = {}
    elif field != "stype":
        data[field] = None


def merge_search_filters(base: SearchFilters, *layers: dict[str, Any]) -> SearchFilters:
    data = model_to_dict(base)
    for src in layers:
        if not src:
            continue
        for field in src.get("clear_fields") or []:
            if isinstance(field, str):
                _clear_field(data, field)
        for k, v in src.items():
            if k == "clear_fields":
                continue
            if v is None:
                continue
            if k == "amenities" and isinstance(v, dict):
                am = dict(data.get("amenities") or {})
                for ak, av in v.items():
                    if av is not None:
                        am[ak] = av
                data["amenities"] = am
            elif k == "property_types" and isinstance(v, list):
                cur = list(data.get("property_types") or [])
                for t in v:
                    if t and t not in cur:
                        cur.append(t)
                data["property_types"] = cur
            elif k == "limit_to_pids" and isinstance(v, list):
                data["limit_to_pids"] = [int(x) for x in v if str(x).isdigit()]
            else:
                data[k] = v
    # Chuẩn hóa amenities thành model
    if "amenities" in data and isinstance(data["amenities"], dict):
        data["amenities"] = model_parse(AmenitiesFilter, data["amenities"])
    return model_parse(SearchFilters, data)
