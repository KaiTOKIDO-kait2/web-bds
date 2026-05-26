#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script sinh 100 dữ liệu property đa dạng cho bảng property
"""
import random
import re
from datetime import datetime, timedelta
from pathlib import Path

# Danh sách ảnh sử dụng từ database hiện tại
IMAGES = ['zillhms1.jpg', 'zillhms2.jpg', 'zillhms3.jpg', 'zillhms4.jpg', 'zillhms6.jpg', 'zillhms7.jpg']
MAP_IMAGES = ['1777904006_mapimage_floorplan_sample.jpg', 'floorplan_sample.jpg']
FLOOR_PLANS = ['floorplan_sample.jpg']

# Các loại bất động sản (khớp bảng property_type sau migration)
PROPERTY_TYPES = [
    (1, 'Căn hộ chung cư'),
    (2, 'Chung cư mini'),
    (3, 'Nhà'),
    (4, 'Biệt thự'),
    (5, 'Nhà mặt phố'),
    (6, 'Nhà trọ'),
    (7, 'Văn phòng'),
]

# Các hướng nhà
DIRECTIONS = ['Đông', 'Tây', 'Nam', 'Bắc', 'Đông Bắc', 'Đông Nam', 'Tây Bắc', 'Tây Nam']

# Loại stype (rent/sale)
STYPES = ['rent', 'sale']

# Status
STATUSES = ['available', 'available', 'available', 'rented', 'maintenance']

# Thành phố chính
CITIES = [
    (79, 'Hà Nội'),
    (91, 'Hải Phòng'),
    (99, 'Đà Nẵng'),
    (106, 'TP.HCM'),
    (111, 'Cần Thơ')
]

WARDS_SQL_PATH = Path('DATABASE FILE/database-new.sql')

# Danh sách người dùng (agent/owner)
USERS = [37, 39, 41, 43, 45, 47, 49, 51, 53, 57, 58]

# Từ khóa tiêu đề
TITLE_PREFIXES = [
    'Cho thuê', 'Bán', 'Cần bán', 'Cần thuê', 'Chuyên cho thuê'
]

TITLE_TYPES = [
    'căn hộ sang trọng', 'nhà phố hiện đại', 'biệt thự cao cấp', 
    'căn hộ tầng cao', 'nhà mặt tiền', 'nhà riêng', 'chung cư mới',
    'tòa nhà văn phòng', 'căn hộ view biển', 'căn hộ full nội thất'
]

TITLE_LOCATIONS = [
    'khu trung tâm', 'gần metro', 'gần trường học', 'khu an ninh',
    'khu thương mại', 'khu dân cư', 'gần bệnh viện'
]

# Nội dung mẫu
CONTENT_TEMPLATE = """<p>Căn {property_type} tuyệt đẹp tại {location}.</p>
<p><strong>Đặc điểm nổi bật:</strong></p>
<ul>
<li>Diện tích: {size} m²</li>
<li>Phòng ngủ: {bedroom}</li>
<li>Phòng tắm: {bathroom}</li>
<li>Hướng: {direction}</li>
<li>Tầng: {floor}</li>
</ul>
<p><strong>Tiện ích:</strong></p>
<ul>
<li>Bãi đỗ xe rộng</li>
<li>Hệ thống an ninh 24/7</li>
<li>Thang máy tốc độ</li>
<li>Khu vui chơi trẻ em</li>
<li>Phòng gym hiện đại</li>
<li>Phòng sinh hoạt cộng đồng</li>
</ul>
<p><strong>Vị trí:</strong> {location}, gần các tiện ích công cộng, trường học, bệnh viện.</p>
<p><strong>Liên hệ:</strong> Xem thông tin chi tiết hoặc gọi trực tiếp để được tư vấn.</p>"""

def generate_price(stype, property_type):
    """Sinh giá hợp lý theo loại bất động sản"""
    price_ranges = {
        'rent': {
            1: (5, 22),      # Căn hộ chung cư
            2: (3, 15),      # Chung cư mini
            3: (8, 30),      # Nhà
            4: (50, 200),    # Biệt thự
            5: (10, 40),     # Nhà mặt phố
            6: (2, 12),      # Nhà trọ
            7: (20, 80)      # Văn phòng
        },
        'sale': {
            1: (1000, 5000),
            2: (800, 3000),
            3: (1500, 8000),
            4: (3000, 15000),
            5: (2000, 10000),
            6: (500, 3000),
            7: (500, 2000)
        }
    }
    
    min_price, max_price = price_ranges[stype].get(property_type, (1000, 5000))
    
    if stype == 'rent':
        return f"{random.randint(min_price, max_price)} triệu/tháng"
    else:
        return f"{random.randint(min_price, max_price)} triệu"

def load_ward_catalog():
    """Đọc danh sách ward thật từ file SQL dump hiện tại."""
    ward_map = {city_id: [] for city_id, _ in CITIES}

    if not WARDS_SQL_PATH.exists():
        return ward_map

    content = WARDS_SQL_PATH.read_text(encoding='utf-8', errors='ignore')
    pattern = re.compile(r"\((\d+),\s*'([^']*)',\s*'([^']*)',\s*(\d+)\)")

    for match in pattern.finditer(content):
        ward_id = int(match.group(1))
        ward_name = match.group(3)
        city_id = int(match.group(4))
        if city_id in ward_map:
            ward_map[city_id].append((ward_id, ward_name))

    return ward_map


WARD_CATALOG = load_ward_catalog()


def pick_ward_for_city(city_id):
    wards = WARD_CATALOG.get(city_id, [])
    if wards:
        return random.choice(wards)
    return None


def generate_property(pid, user_id, city_id, city_name, ward=None):
    """Sinh một bản ghi property"""
    type_id, type_name = random.choice(PROPERTY_TYPES)
    direction = random.choice(DIRECTIONS)
    stype = random.choice(STYPES)
    
    # Số phòng, phòng tắm
    bedroom = random.randint(1, 5)
    bathroom = random.randint(1, 3)
    balcony = random.randint(0, 2)
    kitchen = 1
    hall = 1 if type_id != 7 else 0
    
    # Diện tích
    if type_id == 7:  # Văn phòng
        size = random.randint(50, 500)
    elif type_id == 4:  # Biệt thự
        size = random.randint(200, 800)
    elif type_id == 6:  # Nhà trọ (tòa/khu)
        size = random.randint(100, 500)
    elif type_id in (1, 2):
        size = random.randint(35, 120)
    else:
        size = random.randint(60, 200)
    
    # Tầng
    floor_num = random.randint(1, 30)
    total_floor = floor_num + random.randint(5, 15)
    
    # Tiêu đề
    prefix = random.choice(TITLE_PREFIXES)
    prop_type = random.choice(TITLE_TYPES)
    location_phrase = random.choice(TITLE_LOCATIONS)
    title = f"{prefix} {prop_type} {location_phrase}"
    
    # Địa chỉ
    streets = ['Đường Nguyễn Huệ', 'Đường Lê Lợi', 'Đường Hàng Bông', 'Đường Trần Hưng Đạo', 
               'Đường Võ Văn Kiệt', 'Đường Pasteur', 'Đường Cộng Hòa', 'Đường Bà Triệu']
    if ward is not None:
        ward_id, ward_name = ward
    else:
        ward_id = None
        ward_name = random.choice(['Phường Bến Nghé', 'Phường Đa Kao', 'Phường 1', 'Phường Tân Phú', 'Phường Phú Nhuận'])
    street = random.choice(streets)
    location = street
    
    # Giá
    price = generate_price(stype, type_id)
    
    # Ảnh
    pimage = random.choice(IMAGES)
    pimage1 = random.choice(IMAGES)
    pimage2 = random.choice(IMAGES)
    pimage3 = random.choice(IMAGES)
    pimage4 = random.choice(IMAGES)
    mapimage = random.choice(MAP_IMAGES)
    topmapimage = '1777904006_mapimage_floorplan_sample.jpg'
    groundmapimage = 'floorplan_sample.jpg'
    
    # Status
    status = random.choice(STATUSES)
    
    # Người đăng
    uid = random.choice(USERS)
    
    # Ngày đăng
    days_ago = random.randint(1, 60)
    date = (datetime.now() - timedelta(days=days_ago)).strftime('%Y-%m-%d %H:%M:%S')
    
    # Nội dung
    pcontent = CONTENT_TEMPLATE.format(
        property_type=type_name.lower(),
        location=location,
        size=size,
        bedroom=bedroom,
        bathroom=bathroom,
        direction=direction,
        floor=floor_num
    )
    
    # Tạo SQL
    ward_sql = 'NULL' if ward_id is None else str(ward_id)
    sql = f"""({pid}, '{title}', '{pcontent.replace("'", "\\'")}', '{type_name}', {type_id}, '{direction}', '{stype}', {bedroom}, {bathroom}, {balcony}, {kitchen}, {hall}, '{floor_num}', {size}, '{price}', '{location}', {city_id}, {ward_sql}, '{pimage}', '{pimage1}', '{pimage2}', '{pimage3}', '{pimage4}', {uid}, '{status}', 'approved', 1, NULL, '{mapimage}', '{topmapimage}', '{groundmapimage}', '{total_floor}', '{date}', 0, {random.randint(0, 50)})"""
    
    return sql

def generate_amenity(amenity_id, property_id):
    """Sinh dữ liệu property_amenity"""
    property_age = random.randint(1, 50)
    swimming_pool = random.randint(0, 1)
    parking = random.randint(0, 1)
    gym = random.randint(0, 1)
    near_school = random.randint(0, 1)
    security = random.randint(0, 1)
    near_hospital = random.randint(0, 1)
    near_market = random.randint(0, 1)
    wifi = random.randint(0, 1)
    elevator = random.randint(0, 1)
    cctv = random.randint(0, 1)
    water_source = random.choice(['nuoc_ngam', 'bon_chua'])
    frontage_m = random.choice([None, round(random.uniform(3, 25), 2)])
    access_road_m = random.choice([None, round(random.uniform(2, 12), 2)])
    interior = random.choice([None, 'co_ban', 'day_du', 'khong'])

    def sql_num(v):
        return 'NULL' if v is None else str(v)

    int_sql = 'NULL' if interior is None else f"'{interior}'"

    sql = f"""({amenity_id}, {property_id}, {property_age}, {swimming_pool}, {parking}, {gym}, {near_school}, {security}, {near_hospital}, {near_market}, {wifi}, {elevator}, {cctv}, '{water_source}', {sql_num(frontage_m)}, {sql_num(access_road_m)}, {int_sql}, '2026-05-07 12:00:00', '2026-05-07 12:00:00')"""
    
    return sql

# Sinh dữ liệu
print("Generating 100 properties...")
properties = []
amenities = []

start_pid = 89
start_amenity_id = 6

for i in range(100):
    pid = start_pid + i
    city_id, city_name = random.choice(CITIES)
    ward = pick_ward_for_city(city_id)
    
    prop_sql = generate_property(pid, USERS[i % len(USERS)], city_id, city_name, ward)
    properties.append(prop_sql)
    
    amenity_sql = generate_amenity(start_amenity_id + i, pid)
    amenities.append(amenity_sql)

# Viết file SQL
with open('DATABASE FILE/insert_100_properties.sql', 'w', encoding='utf-8') as f:
    f.write("-- Insert 100 properties\n")
    f.write("INSERT INTO `property` (`pid`, `title`, `pcontent`, `type`, `type_id`, `direction`, `stype`, `bedroom`, `bathroom`, `balcony`, `kitchen`, `hall`, `floor`, `size`, `price`, `location`, `city_id`, `ward_id`, `pimage`, `pimage1`, `pimage2`, `pimage3`, `pimage4`, `uid`, `status`, `approval_status`, `approval_seen`, `reviewed_at`, `mapimage`, `topmapimage`, `groundmapimage`, `totalfloor`, `date`, `isFeatured`, `view_count`) VALUES\n")
    f.write(',\n'.join(properties))
    f.write(";\n\n")
    
    f.write("-- Insert property amenities\n")
    f.write("INSERT INTO `property_amenity` (`id`, `property_id`, `property_age`, `swimming_pool`, `parking`, `gym`, `near_school`, `security`, `near_hospital`, `near_market`, `wifi`, `elevator`, `cctv`, `water_source`, `frontage_m`, `access_road_m`, `interior_level`, `created_at`, `updated_at`) VALUES\n")
    f.write(',\n'.join(amenities))
    f.write(";\n")

print(f"✓ File created: DATABASE FILE/insert_100_properties.sql")
print(f"✓ Generated 100 properties (PID: {start_pid}-{start_pid+99})")
print(f"✓ Generated 100 amenity records (ID: {start_amenity_id}-{start_amenity_id+99})")
