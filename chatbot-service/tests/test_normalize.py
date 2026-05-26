import unittest

from app.normalize import normalize_user_text


class TestNormalize(unittest.TestCase):
    def test_pn_price(self):
        s = normalize_user_text("2pn dưới 20tr")
        self.assertIn("phòng ngủ", s)
        self.assertIn("triệu", s)

    def test_triệu(self):
        s = normalize_user_text("dưới 15 triệu")
        self.assertIn("triệu", s)

    def test_cu_to_trieu(self):
        s = normalize_user_text("khoảng 50 củ")
        self.assertIn("50 triệu", s)


if __name__ == "__main__":
    unittest.main()
