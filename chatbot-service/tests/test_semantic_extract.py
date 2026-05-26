import unittest
from types import SimpleNamespace
from unittest.mock import patch

from app import semantic_extract
from app.semantic_extract import extract_semantic_filters


class FakeEmbeddingModel:
    def encode(self, sentences, normalize_embeddings=True):
        return [self._vector_for(s) for s in sentences]

    def _vector_for(self, sentence):
        text = sentence.lower()
        if any(word in text for word in ["wifi", "wi-fi", "internet", "mạng", "mang"]):
            return [1.0, 0.0, 0.0]
        if any(word in text for word in ["bãi xe", "chỗ đậu", "chỗ để xe", "nơi để xe", "gửi xe", "parking"]):
            return [0.0, 1.0, 0.0]
        if any(word in text for word in ["an ninh", "bảo vệ", "security", "an toàn"]):
            return [0.0, 0.0, 1.0]
        return [0.0, 0.0, 0.0]


class TestSemanticExtract(unittest.TestCase):
    def setUp(self):
        semantic_extract._concept_vectors.cache_clear()
        semantic_extract._load_model.cache_clear()

    def tearDown(self):
        semantic_extract._concept_vectors.cache_clear()
        semantic_extract._load_model.cache_clear()

    def _settings(self, enabled=True, threshold=0.9):
        return SimpleNamespace(
            embedding_enabled=enabled,
            embedding_model="fake-model",
            embedding_threshold=threshold,
        )

    def test_wifi_slang(self):
        with patch("app.semantic_extract.get_settings", return_value=self._settings()), patch(
            "app.semantic_extract._load_model", return_value=FakeEmbeddingModel()
        ):
            r = extract_semantic_filters("có căn nào gần phường nam triệu mạng mẽo đầy đủ không")
        self.assertEqual(r.get("amenities", {}).get("wifi"), True)

    def test_parking_phrase(self):
        with patch("app.semantic_extract.get_settings", return_value=self._settings()), patch(
            "app.semantic_extract._load_model", return_value=FakeEmbeddingModel()
        ):
            r = extract_semantic_filters("nhà có nơi để xe")
        self.assertEqual(r.get("amenities", {}).get("parking"), True)

    def test_security_phrase(self):
        with patch("app.semantic_extract.get_settings", return_value=self._settings()), patch(
            "app.semantic_extract._load_model", return_value=FakeEmbeddingModel()
        ):
            r = extract_semantic_filters("khu này an ninh tốt")
        self.assertEqual(r.get("amenities", {}).get("security"), True)

    def test_unrelated_text(self):
        with patch("app.semantic_extract.get_settings", return_value=self._settings()), patch(
            "app.semantic_extract._load_model", return_value=FakeEmbeddingModel()
        ):
            r = extract_semantic_filters("tôi muốn tìm căn nào đẹp")
        self.assertEqual(r, {})

    def test_disabled(self):
        with patch("app.semantic_extract.get_settings", return_value=self._settings(enabled=False)):
            r = extract_semantic_filters("mạng mẽo đầy đủ")
        self.assertEqual(r, {})


if __name__ == "__main__":
    unittest.main()
