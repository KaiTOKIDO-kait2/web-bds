import unittest

from app.rules_extract import extract_rules, wants_budget_friendly, wants_cheapest, wants_priciest


class TestRulesExtract(unittest.TestCase):
    def test_phuong1_hcm(self):
        n = "căn hộ 2 phòng ngủ phường 1 dưới 20 triệu"
        r = extract_rules(n)
        # Không hardcode city_id; chỉ tạo hint theo tên để resolve động từ DB
        self.assertIsNone(r.get("city_id"))
        self.assertEqual(r.get("ward_name_hint"), "Phường 1")
        self.assertEqual(r.get("keyword"), "Phường 1")
        self.assertEqual(r.get("bedrooms_min"), 2)
        self.assertAlmostEqual(r.get("price_max_million"), 20.0)

    def test_cheapest_intent(self):
        self.assertTrue(wants_cheapest("căn rẻ nhất"))
        self.assertTrue(wants_priciest("căn đắt nhất"))

    def test_budget_friendly_sets_sort_without_price_guess(self):
        r = extract_rules("có căn nào giá rẻ ở hà nội không")
        self.assertTrue(wants_budget_friendly("giá mềm vừa túi tiền"))
        self.assertEqual(r.get("sort"), "price_asc")
        self.assertEqual(r.get("keyword"), "Hà Nội")
        self.assertIsNone(r.get("price_max_million"))

    def test_toa_nha_type(self):
        r = extract_rules("tìm tòa nhà ở hcm 5 phòng ngủ")
        self.assertIn("Tòa nhà", r.get("property_types", []))
        self.assertNotIn("Nhà", r.get("property_types", []))

    def test_hai_phong_xa_nghi_duong(self):
        n = "có căn hộ nào giá rẻ tầm 100-150m2 ở gần xã nghi dương thành phố hải phòng không"
        r = extract_rules(n)
        self.assertEqual(r.get("size_min"), 100)
        self.assertEqual(r.get("size_max"), 150)
        self.assertEqual(r.get("ward_name_hint"), "Xã Nghi Dương")
        # keyword ưu tiên địa danh chi tiết hơn (xã) để match LIKE
        self.assertIn("Nghi", r.get("keyword", ""))

    def test_wifi_slang(self):
        n = "có căn nào có ít phòng ngủ gần phường nam triệu , mạng mẽo đầy đủ không"
        r = extract_rules(n)
        self.assertEqual(r.get("ward_name_hint"), "Phường Nam Triệu")
        self.assertEqual(r.get("amenities", {}).get("wifi"), True)

    def test_xa_tam_hai_not_greedy(self):
        n = "có căn chung cư nào gần xã tam hải có nơi để xe và gần bệnh viện không"
        r = extract_rules(n)
        self.assertEqual(r.get("ward_name_hint"), "Xã Tam Hải")
        self.assertEqual(r.get("keyword"), "Xã Tam Hải")


if __name__ == "__main__":
    unittest.main()
