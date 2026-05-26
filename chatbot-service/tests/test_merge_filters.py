import unittest

from app.merge_filters import merge_search_filters
from app.schemas import AmenitiesFilter, SearchFilters


class TestMergeFilters(unittest.TestCase):
    def test_merge_layers(self):
        base = SearchFilters(stype="rent", city_id=2)
        out = merge_search_filters(
            base,
            {"bedrooms_min": 2, "amenities": {"swimming_pool": True}},
            {"price_max_million": 18.0},
        )
        self.assertEqual(out.bedrooms_min, 2)
        self.assertEqual(out.price_max_million, 18.0)
        self.assertEqual(out.amenities.swimming_pool, True)

    def test_clear_fields_then_apply_new_values(self):
        base = SearchFilters(
            stype="rent",
            ward_id=10,
            ward_name_hint="Xã Quảng Trực",
            keyword="Xã Quảng Trực",
            property_types=["Căn hộ"],
            amenities=AmenitiesFilter(gym=True, wifi=True),
        )
        out = merge_search_filters(
            base,
            {
                "clear_fields": ["ward_id", "ward_name_hint", "keyword", "property_types"],
                "property_types": ["Nhà"],
                "amenities": {"gym": False},
            },
        )
        self.assertIsNone(out.ward_id)
        self.assertIsNone(out.ward_name_hint)
        self.assertIsNone(out.keyword)
        self.assertEqual(out.property_types, ["Nhà"])
        self.assertEqual(out.amenities.gym, False)
        self.assertEqual(out.amenities.wifi, True)


if __name__ == "__main__":
    unittest.main()
