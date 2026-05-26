import unittest
from unittest.mock import patch

from app.property_search import _rank_rows
from app.schemas import SearchFilters


class TestHybridRanking(unittest.TestCase):
    def test_semantic_score_can_promote_relevant_candidate(self):
        rows = [
            {
                "pid": 1,
                "title": "Tin cu",
                "type": "Nhà",
                "city_id": 1,
                "ward_id": 1,
                "bedroom": 1,
                "price": "20",
                "view_count": 0,
                "embedding_json": "[0.0, 1.0]",
            },
            {
                "pid": 2,
                "title": "Can ho gan truong co wifi",
                "type": "Căn hộ",
                "city_id": 1,
                "ward_id": 1,
                "bedroom": 2,
                "price": "18",
                "view_count": 0,
                "embedding_json": "[1.0, 0.0]",
            },
        ]
        filters = SearchFilters(
            city_id=1,
            ward_id=1,
            property_types=["Căn hộ"],
            bedrooms_min=2,
            price_max_million=20,
        )
        with patch("app.property_search.encode_text", return_value=[1.0, 0.0]):
            ranked = _rank_rows(rows, filters, "can ho gan truong co wifi")

        self.assertEqual(ranked[0]["pid"], 2)
        self.assertGreater(ranked[0]["_semantic_score"], ranked[1]["_semantic_score"])
        self.assertIn("Nội dung gần nghĩa với yêu cầu", ranked[0]["_matched_reasons"])


if __name__ == "__main__":
    unittest.main()
