<?php
class Property {
    private $db;
    private $columnCache = [];
    private $tableCache = [];

    public function __construct() {
        $this->db = new Database;
        $this->ensureApprovalColumns();
        $this->ensureFavoritesTable();
        $this->ensureInquiriesTable();
        $this->ensurePropertyLocationColumns();
        $this->ensureAmenitiesTable();
    }

    private function hasColumn($table, $column) {
        $cacheKey = $table . '.' . $column;
        if (array_key_exists($cacheKey, $this->columnCache)) {
            return $this->columnCache[$cacheKey];
        }

        try {
            $this->db->query("SHOW COLUMNS FROM {$table} LIKE :column");
            $this->db->bind(':column', $column);
            $result = $this->db->single();
            $this->columnCache[$cacheKey] = !empty($result);
        } catch (Throwable $e) {
            $this->columnCache[$cacheKey] = false;
        }

        return $this->columnCache[$cacheKey];
    }

    private function hasTable($table) {
        if (array_key_exists($table, $this->tableCache)) {
            return $this->tableCache[$table];
        }

        try {
            $this->db->query("SHOW TABLES LIKE :table");
            $this->db->bind(':table', $table);
            $result = $this->db->single();
            $this->tableCache[$table] = !empty($result);
        } catch (Throwable $e) {
            $this->tableCache[$table] = false;
        }

        return $this->tableCache[$table];
    }

    private function canUsePropertyTypeRelation() {
        return $this->hasTable('property_type') && $this->hasColumn('property', 'type_id');
    }

    private function getPropertyTypeNameById($typeId) {
        if (!$this->hasTable('property_type') || empty($typeId)) {
            return null;
        }

        $this->db->query("SELECT name FROM property_type WHERE id = :id LIMIT 1");
        $this->db->bind(':id', (int) $typeId);
        $row = $this->db->single();
        return !empty($row['name']) ? (string) $row['name'] : null;
    }

    private function getPropertyTypeIdByName($typeName) {
        $typeName = trim((string) $typeName);
        if (!$this->hasTable('property_type') || $typeName === '') {
            return null;
        }

        $this->db->query("SELECT id FROM property_type WHERE name = :name LIMIT 1");
        $this->db->bind(':name', $typeName);
        $row = $this->db->single();
        if (!empty($row['id'])) {
            return (int) $row['id'];
        }

        $this->db->query("SELECT id FROM property_type WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) LIMIT 1");
        $this->db->bind(':name', $typeName);
        $row = $this->db->single();
        return !empty($row['id']) ? (int) $row['id'] : null;
    }

    private function resolvePropertyTypePayload($data) {
        $rawTypeName = trim((string) ($data['ptype'] ?? ($data['type'] ?? '')));
        $rawTypeId = isset($data['type_id']) && $data['type_id'] !== '' ? (int) $data['type_id'] : 0;

        $typeId = null;
        $typeName = $rawTypeName;

        if ($this->canUsePropertyTypeRelation()) {
            if ($rawTypeId > 0) {
                $typeId = $rawTypeId;
                if ($typeName === '') {
                    $nameFromId = $this->getPropertyTypeNameById($typeId);
                    if ($nameFromId !== null) {
                        $typeName = $nameFromId;
                    }
                }
            } elseif ($typeName !== '') {
                $resolvedTypeId = $this->getPropertyTypeIdByName($typeName);
                if ($resolvedTypeId !== null) {
                    $typeId = $resolvedTypeId;
                    $nameFromId = $this->getPropertyTypeNameById($resolvedTypeId);
                    if ($nameFromId !== null) {
                        $typeName = $nameFromId;
                    }
                }
            }
        }

        return [
            'name' => $typeName,
            'id' => $typeId,
        ];
    }

    public function getPropertyTypes($activeOnly = true) {
        if (!$this->hasTable('property_type')) {
            return [];
        }

        $sql = "SELECT id, name, slug, is_active, sort_order FROM property_type";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, name ASC";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    private function ensurePropertyLocationColumns() {
        $queries = [
            "ALTER TABLE property ADD COLUMN IF NOT EXISTS city_id INT NULL AFTER city",
            "ALTER TABLE property ADD COLUMN IF NOT EXISTS ward_id INT NULL AFTER city_id",
            "ALTER TABLE property ADD INDEX IF NOT EXISTS idx_property_city_id (city_id)",
            "ALTER TABLE property ADD INDEX IF NOT EXISTS idx_property_ward_id (ward_id)"
        ];

        foreach ($queries as $sql) {
            try {
                $this->db->query($sql);
                $this->db->execute();
            } catch (Throwable $e) {
                // Ignore schema-alter failures in restricted environments.
            }
        }
    }

    private function normalizeLocationPayload($data) {
        $city = isset($data['city']) ? trim((string) $data['city']) : '';
        $cityId = isset($data['city_id']) && $data['city_id'] !== '' ? (int) $data['city_id'] : 0;
        $wardId = isset($data['ward_id']) && $data['ward_id'] !== '' ? (int) $data['ward_id'] : 0;

        if ($cityId > 0) {
            $this->db->query("SELECT cid, cname FROM city WHERE cid = :id LIMIT 1");
            $this->db->bind(':id', $cityId);
            $cityRow = $this->db->single();
            if ($cityRow) {
                $city = $cityRow['cname'];
            } else {
                $cityId = 0;
            }
        }

        if ($wardId > 0) {
            $this->db->query("SELECT wid, wname, city_id FROM wards WHERE wid = :id LIMIT 1");
            $this->db->bind(':id', $wardId);
            $wardRow = $this->db->single();
            if ($wardRow) {
                if (empty($cityId) && !empty($wardRow['city_id'])) {
                    $cityId = (int) $wardRow['city_id'];
                }
            } else {
                $wardId = 0;
            }
        }

        if (empty($cityId) && $city !== '') {
            $this->db->query("SELECT cid, cname FROM city WHERE cname = :name LIMIT 1");
            $this->db->bind(':name', $city);
            $cityRow = $this->db->single();
            if ($cityRow) {
                $cityId = (int) $cityRow['cid'];
                $city = $cityRow['cname'];
            }
        }

        return [
            'city' => $city,
            'city_id' => $cityId > 0 ? $cityId : null,
            'ward_id' => $wardId > 0 ? $wardId : null,
        ];
    }

    private function getPropertyLocationColumnSupport() {
        return [
            'city' => $this->hasColumn('property', 'city'),
            'city_id' => $this->hasColumn('property', 'city_id'),
            'ward_id' => $this->hasColumn('property', 'ward_id'),
        ];
    }

    private function appendLocationColumnsForInsert(&$columns, &$placeholders, $locationSupport) {
        if (!empty($locationSupport['city'])) {
            $columns[] = 'city';
            $placeholders[] = ':city';
        }
        if (!empty($locationSupport['city_id'])) {
            $columns[] = 'city_id';
            $placeholders[] = ':city_id';
        }
        if (!empty($locationSupport['ward_id'])) {
            $columns[] = 'ward_id';
            $placeholders[] = ':ward_id';
        }
    }

    private function appendLocationAssignmentsForUpdate(&$setParts, $locationSupport) {
        if (!empty($locationSupport['city'])) {
            $setParts[] = 'city=:city';
        }
        if (!empty($locationSupport['city_id'])) {
            $setParts[] = 'city_id=:city_id';
        }
        if (!empty($locationSupport['ward_id'])) {
            $setParts[] = 'ward_id=:ward_id';
        }
    }

    private function bindLocationValues($location, $locationSupport) {
        if (!empty($locationSupport['city'])) {
            $this->db->bind(':city', $location['city']);
        }
        if (!empty($locationSupport['city_id'])) {
            $this->db->bind(':city_id', $location['city_id']);
        }
        if (!empty($locationSupport['ward_id'])) {
            $this->db->bind(':ward_id', $location['ward_id']);
        }
    }

    private function hydrateLocationRows($rows) {
        if (empty($rows) || !is_array($rows)) {
            return $rows;
        }

        $cityIds = [];
        $wardIds = [];

        foreach ($rows as $row) {
            if ((empty($row['city']) || !isset($row['city'])) && !empty($row['city_id'])) {
                $cityIds[] = (int) $row['city_id'];
            }
            if ((empty($row['ward']) || !isset($row['ward'])) && !empty($row['ward_id'])) {
                $wardIds[] = (int) $row['ward_id'];
            }
        }

        $cityMap = [];
        $wardMap = [];

        $cityIds = array_values(array_unique(array_filter($cityIds)));
        if (!empty($cityIds)) {
            $cityPlaceholders = [];
            foreach ($cityIds as $index => $cityId) {
                $cityPlaceholders[] = ':city_id_' . $index;
            }
            $this->db->query("SELECT cid, cname FROM city WHERE cid IN (" . implode(',', $cityPlaceholders) . ")");
            foreach ($cityIds as $index => $cityId) {
                $this->db->bind(':city_id_' . $index, $cityId);
            }
            $cityRows = $this->db->resultSet();
            foreach ($cityRows as $cityRow) {
                $cityMap[(int) $cityRow['cid']] = $cityRow['cname'];
            }
        }

        $wardIds = array_values(array_unique(array_filter($wardIds)));
        if (!empty($wardIds)) {
            $wardPlaceholders = [];
            foreach ($wardIds as $index => $wardId) {
                $wardPlaceholders[] = ':ward_id_' . $index;
            }
            $this->db->query("SELECT wid, wname FROM wards WHERE wid IN (" . implode(',', $wardPlaceholders) . ")");
            foreach ($wardIds as $index => $wardId) {
                $this->db->bind(':ward_id_' . $index, $wardId);
            }
            $wardRows = $this->db->resultSet();
            foreach ($wardRows as $wardRow) {
                $wardMap[(int) $wardRow['wid']] = $wardRow['wname'];
            }
        }

        foreach ($rows as &$row) {
            if ((empty($row['city']) || !isset($row['city'])) && !empty($row['city_id'])) {
                $cityId = (int) $row['city_id'];
                if (isset($cityMap[$cityId])) {
                    $row['city'] = $cityMap[$cityId];
                }
            }
            if ((empty($row['ward']) || !isset($row['ward'])) && !empty($row['ward_id'])) {
                $wardId = (int) $row['ward_id'];
                if (isset($wardMap[$wardId])) {
                    $row['ward'] = $wardMap[$wardId];
                }
            }
        }
        unset($row);

        return $rows;
    }

    private function hydrateLocationRow($row) {
        if (empty($row) || !is_array($row)) {
            return $row;
        }

        $rows = $this->hydrateLocationRows([$row]);
        return $rows[0];
    }

    private function ensureApprovalColumns() {
        $queries = [
            "ALTER TABLE property ADD COLUMN IF NOT EXISTS approval_status VARCHAR(20) NOT NULL DEFAULT 'approved' AFTER status",
            "ALTER TABLE property ADD COLUMN IF NOT EXISTS approval_seen TINYINT(1) NOT NULL DEFAULT 1 AFTER approval_status",
            "ALTER TABLE property ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL DEFAULT NULL AFTER approval_seen"
        ];

        foreach ($queries as $sql) {
            try {
                $this->db->query($sql);
                $this->db->execute();
            } catch (Throwable $e) {
                // Keep the app working even if the column already exists or the DB user cannot alter schema.
            }
        }
    }

    private function publicApprovalWhere() {
        return "property.approval_status = 'approved'";
    }

    private function ensureFavoritesTable() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS property_favorite (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uid INT NOT NULL,
                pid INT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_property (uid, pid),
                INDEX idx_favorite_uid (uid),
                INDEX idx_favorite_pid (pid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->execute();
        } catch (Throwable $e) {
            // Keep the app working even if schema changes are not permitted.
        }
    }

    private function ensureInquiriesTable() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS property_inquiry (
                id INT AUTO_INCREMENT PRIMARY KEY,
                property_id INT NOT NULL,
                agent_uid INT NOT NULL,
                inquirer_uid INT NULL,
                inquirer_name VARCHAR(120) NOT NULL,
                work_email VARCHAR(160) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                requirement TEXT NOT NULL,
                status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
                case_status ENUM('new','contacted','scheduled','viewed','completed','cancelled') NOT NULL DEFAULT 'new',
                desired_budget VARCHAR(120) NULL,
                desired_area VARCHAR(255) NULL,
                desired_move_in_time VARCHAR(120) NULL,
                appointment_status ENUM('none','pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'none',
                appointment_requested_at DATETIME NULL,
                appointment_confirmed_at DATETIME NULL,
                appointment_note TEXT NULL,
                viewed_at DATETIME NULL,
                result_note TEXT NULL,
                workflow_updated_at DATETIME NULL,
                contacted_at DATETIME NULL,
                notes TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_property_inquiry_agent (agent_uid),
                INDEX idx_property_inquiry_property (property_id),
                INDEX idx_property_inquiry_created (created_at),
                INDEX idx_property_inquiry_status (status),
                INDEX idx_property_inquiry_case_status (case_status),
                INDEX idx_property_inquiry_appointment_status (appointment_status),
                INDEX idx_property_inquiry_workflow_updated_at (workflow_updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->execute();
        } catch (Throwable $e) {
            // Keep the app working when schema migration is unavailable.
        }
    }

    private function ensureAmenitiesTable() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS property_amenity (
                id INT AUTO_INCREMENT PRIMARY KEY,
                property_id INT NOT NULL,
                property_age INT DEFAULT NULL,
                swimming_pool TINYINT(1) NOT NULL DEFAULT 0,
                parking TINYINT(1) NOT NULL DEFAULT 0,
                gym TINYINT(1) NOT NULL DEFAULT 0,
                near_school TINYINT(1) NOT NULL DEFAULT 0,
                security TINYINT(1) NOT NULL DEFAULT 0,
                near_hospital TINYINT(1) NOT NULL DEFAULT 0,
                near_market TINYINT(1) NOT NULL DEFAULT 0,
                wifi TINYINT(1) NOT NULL DEFAULT 0,
                elevator TINYINT(1) NOT NULL DEFAULT 0,
                cctv TINYINT(1) NOT NULL DEFAULT 0,
                water_source ENUM('nuoc_ngam', 'bon_chua') DEFAULT NULL,
                frontage_m DECIMAL(10,2) DEFAULT NULL,
                access_road_m DECIMAL(10,2) DEFAULT NULL,
                interior_level ENUM('co_ban', 'day_du', 'khong') DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_property_amenity_property_id (property_id),
                INDEX idx_property_amenity_property_id (property_id),
                CONSTRAINT fk_property_amenity_property
                    FOREIGN KEY (property_id) REFERENCES property(pid)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->execute();
        } catch (Throwable $e) {
            // Keep the app working when schema migration is unavailable.
        }
    }

    private function getDefaultAmenityPayload() {
        return [
            'property_age' => null,
            'swimming_pool' => 0,
            'parking' => 0,
            'gym' => 0,
            'near_school' => 0,
            'security' => 0,
            'near_hospital' => 0,
            'near_market' => 0,
            'wifi' => 0,
            'elevator' => 0,
            'cctv' => 0,
            'water_source' => '',
            'frontage_m' => null,
            'access_road_m' => null,
            'interior_level' => '',
        ];
    }

    private function normalizeAmenityFlag($value) {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'yes', 'true', 'on', 'co', 'có'], true) ? 1 : 0;
    }

    private function normalizeWaterSource($value) {
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['nuoc_ngam', 'nước ngầm', 'nuoc ngam'], true)) {
            return 'nuoc_ngam';
        }

        if (in_array($normalized, ['bon_chua', 'bồn chứa', 'bon chua'], true)) {
            return 'bon_chua';
        }

        return '';
    }

    private function normalizeOptionalMeter($value) {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            $n = (float) $value;
        } else {
            $s = trim((string) $value);
            $s = preg_replace('/\s*m²\s*$/iu', '', $s);
            $s = preg_replace('/\s*mét\s*$/iu', '', $s);
            $s = preg_replace('/\s*m\s*$/iu', '', $s);
            $s = str_replace(',', '.', preg_replace('/[^\d.,-]/u', '', $s));
            if ($s === '' || !is_numeric($s)) {
                return null;
            }
            $n = (float) $s;
        }
        if ($n < 0 || $n > 999999.99) {
            return null;
        }
        return round($n, 2);
    }

    private function normalizeInteriorLevel($value) {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }
        $slug = strtolower($raw);
        if (in_array($slug, ['co_ban', 'day_du', 'khong'], true)) {
            return $slug;
        }

        $lower = mb_strtolower($raw, 'UTF-8');
        if (str_contains($lower, 'đầy đủ') || str_contains($lower, 'day du')) {
            return 'day_du';
        }
        if (str_contains($lower, 'cơ bản') || str_contains($lower, 'co ban')) {
            return 'co_ban';
        }
        if (str_contains($lower, 'không nội thất') || str_contains($lower, 'khong noi that') || $lower === 'không' || $lower === 'khong') {
            return 'khong';
        }

        return '';
    }

    private function interiorLevelLabel($value) {
        if ($value === 'co_ban') {
            return 'Cơ bản';
        }
        if ($value === 'day_du') {
            return 'Đầy đủ';
        }
        if ($value === 'khong') {
            return 'Không nội thất';
        }
        return '';
    }

    private function formatMeterDisplay($value) {
        if ($value === null || $value === '') {
            return 'Chưa cập nhật';
        }
        if (!is_numeric($value)) {
            return 'Chưa cập nhật';
        }
        $n = (float) $value;
        $s = number_format($n, 2, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');
        return $s . ' m';
    }

    private function normalizePropertyAmenityPayload($data) {
        return [
            'property_age' => (isset($data['property_age']) && $data['property_age'] !== '')
                ? max(0, (int) $data['property_age'])
                : null,
            'swimming_pool' => $this->normalizeAmenityFlag($data['swimming_pool'] ?? 0),
            'parking' => $this->normalizeAmenityFlag($data['parking'] ?? 0),
            'gym' => $this->normalizeAmenityFlag($data['gym'] ?? 0),
            'near_school' => $this->normalizeAmenityFlag($data['near_school'] ?? 0),
            'security' => $this->normalizeAmenityFlag($data['security'] ?? 0),
            'near_hospital' => $this->normalizeAmenityFlag($data['near_hospital'] ?? 0),
            'near_market' => $this->normalizeAmenityFlag($data['near_market'] ?? 0),
            'wifi' => $this->normalizeAmenityFlag($data['wifi'] ?? 0),
            'elevator' => $this->normalizeAmenityFlag($data['elevator'] ?? 0),
            'cctv' => $this->normalizeAmenityFlag($data['cctv'] ?? 0),
            'water_source' => $this->normalizeWaterSource($data['water_source'] ?? ''),
            'frontage_m' => $this->normalizeOptionalMeter($data['frontage_m'] ?? null),
            'access_road_m' => $this->normalizeOptionalMeter($data['access_road_m'] ?? null),
            'interior_level' => $this->normalizeInteriorLevel($data['interior_level'] ?? ''),
        ];
    }

    private function amenityFlagLabel($value) {
        return (int) $value === 1 ? 'Có' : 'Không';
    }

    private function waterSourceLabel($value) {
        if ($value === 'nuoc_ngam') {
            return 'Nước ngầm';
        }
        if ($value === 'bon_chua') {
            return 'Bồn chứa';
        }
        return '';
    }

    private function buildFeatureHtmlFromAmenities($amenities) {
        $age = isset($amenities['property_age']) && $amenities['property_age'] !== null
            ? (int) $amenities['property_age']
            : null;
        $waterSource = $this->waterSourceLabel((string) ($amenities['water_source'] ?? ''));
        $interior = $this->interiorLevelLabel((string) ($amenities['interior_level'] ?? ''));

        return implode("\n", [
            '<!---feature area start--->',
            '<div class="col-md-4">',
            '    <ul>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Tuổi bất động sản: </span>' . htmlspecialchars($age !== null ? ($age . ' năm') : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Mặt tiền (m): </span>' . htmlspecialchars($this->formatMeterDisplay($amenities['frontage_m'] ?? null), ENT_QUOTES, 'UTF-8') . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Đường vào (m): </span>' . htmlspecialchars($this->formatMeterDisplay($amenities['access_road_m'] ?? null), ENT_QUOTES, 'UTF-8') . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Nội thất: </span>' . htmlspecialchars($interior !== '' ? $interior : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Hồ bơi: </span>' . $this->amenityFlagLabel($amenities['swimming_pool'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Bãi đỗ xe: </span>' . $this->amenityFlagLabel($amenities['parking'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Phòng gym: </span>' . $this->amenityFlagLabel($amenities['gym'] ?? 0) . '</li>',
            '    </ul>',
            '</div>',
            '<div class="col-md-4">',
            '    <ul>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Gần trường học: </span>' . $this->amenityFlagLabel($amenities['near_school'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Bảo vệ: </span>' . $this->amenityFlagLabel($amenities['security'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Gần bệnh viện: </span>' . $this->amenityFlagLabel($amenities['near_hospital'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Gần chợ: </span>' . $this->amenityFlagLabel($amenities['near_market'] ?? 0) . '</li>',
            '    </ul>',
            '</div>',
            '<div class="col-md-4">',
            '    <ul>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Wifi: </span>' . $this->amenityFlagLabel($amenities['wifi'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Thang máy: </span>' . $this->amenityFlagLabel($amenities['elevator'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">CCTV Camera: </span>' . $this->amenityFlagLabel($amenities['cctv'] ?? 0) . '</li>',
            '        <li class="mb-3"><span class="text-secondary font-weight-bold">Nguồn nước: </span>' . htmlspecialchars($waterSource !== '' ? $waterSource : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') . '</li>',
            '    </ul>',
            '</div>',
            '<!---feature area end---->',
        ]);
    }

    private function hasLegacyFeatureColumn() {
        return $this->hasColumn('property', 'feature');
    }

    private function extractAmenitiesFromFeatureHtml($featureHtml) {
        $featureHtml = html_entity_decode((string) $featureHtml, ENT_QUOTES, 'UTF-8');
        $defaults = $this->getDefaultAmenityPayload();

        if (trim($featureHtml) === '') {
            return $defaults;
        }

        $labelMap = [
            'Tuổi bất động sản' => 'property_age',
            'Hồ bơi' => 'swimming_pool',
            'Bãi đỗ xe' => 'parking',
            'Phòng gym' => 'gym',
            'Gần trường học' => 'near_school',
            'Bảo vệ' => 'security',
            'An ninh' => 'security',
            'Gần bệnh viện' => 'near_hospital',
            'Gần chợ' => 'near_market',
            'Wifi' => 'wifi',
            'Thang máy' => 'elevator',
            'CCTV Camera' => 'cctv',
            'Camera CCTV' => 'cctv',
            'Nguồn nước' => 'water_source',
            'Mặt tiền (m)' => 'frontage_m',
            'Mặt tiền' => 'frontage_m',
            'Đường vào (m)' => 'access_road_m',
            'Đường vào' => 'access_road_m',
            'Nội thất' => 'interior_level',
        ];

        if (preg_match_all('/<li[^>]*>\s*<span[^>]*>\s*([^:]+):\s*<\/span>\s*(.*?)\s*<\/li>/isu', $featureHtml, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $label = trim(strip_tags($match[1]));
                $value = trim(strip_tags($match[2]));
                if (!isset($labelMap[$label])) {
                    continue;
                }

                $field = $labelMap[$label];
                if ($field === 'property_age') {
                    if (preg_match('/\d+/', $value, $ageMatch)) {
                        $defaults[$field] = (int) $ageMatch[0];
                    }
                } elseif ($field === 'water_source') {
                    $defaults[$field] = $this->normalizeWaterSource($value);
                } elseif ($field === 'frontage_m' || $field === 'access_road_m') {
                    $defaults[$field] = $this->normalizeOptionalMeter($value);
                } elseif ($field === 'interior_level') {
                    $defaults[$field] = $this->normalizeInteriorLevel($value);
                } else {
                    $defaults[$field] = $this->normalizeAmenityFlag($value);
                }
            }
        }

        return $defaults;
    }

    private function getPropertyAmenities($propertyId, $featureHtml = '') {
        if (!$this->hasTable('property_amenity')) {
            return $this->extractAmenitiesFromFeatureHtml($featureHtml);
        }

        try {
            $selectCols = 'property_age, swimming_pool, parking, gym, near_school, security, near_hospital, near_market, wifi, elevator, cctv, water_source';
            if ($this->hasColumn('property_amenity', 'frontage_m')) {
                $selectCols .= ', frontage_m, access_road_m, interior_level';
            }
            $this->db->query("SELECT {$selectCols}
                FROM property_amenity
                WHERE property_id = :property_id
                LIMIT 1");
            $this->db->bind(':property_id', (int) $propertyId);
            $row = $this->db->single();
            if (!$row) {
                $amenities = $this->extractAmenitiesFromFeatureHtml($featureHtml);
                if (trim((string) $featureHtml) !== '') {
                    $this->savePropertyAmenities((int) $propertyId, $amenities);
                }
                return $amenities;
            }

            return array_merge($this->getDefaultAmenityPayload(), $row);
        } catch (Throwable $e) {
            return $this->extractAmenitiesFromFeatureHtml($featureHtml);
        }
    }

    private function attachAmenitiesToPropertyRow($row) {
        if (empty($row) || !is_array($row) || empty($row['pid'])) {
            return $row;
        }

        $amenities = $this->getPropertyAmenities((int) $row['pid'], $row['feature'] ?? '');
        $row = array_merge($row, $amenities);
        $row['feature_html'] = $this->buildFeatureHtmlFromAmenities($amenities);

        if (empty($row['feature'])) {
            $row['feature'] = $row['feature_html'];
        }

        return $row;
    }

    private function savePropertyAmenities($propertyId, $data) {
        if (!$this->hasTable('property_amenity')) {
            return true;
        }

        $amenities = $this->normalizePropertyAmenityPayload($data);

        if ($this->hasColumn('property_amenity', 'frontage_m')) {
            $this->db->query("INSERT INTO property_amenity (
                    property_id, property_age, swimming_pool, parking, gym, near_school, security, near_hospital, near_market, wifi, elevator, cctv, water_source,
                    frontage_m, access_road_m, interior_level
                ) VALUES (
                    :property_id, :property_age, :swimming_pool, :parking, :gym, :near_school, :security, :near_hospital, :near_market, :wifi, :elevator, :cctv, :water_source,
                    :frontage_m, :access_road_m, :interior_level
                )
                ON DUPLICATE KEY UPDATE
                    property_age = VALUES(property_age),
                    swimming_pool = VALUES(swimming_pool),
                    parking = VALUES(parking),
                    gym = VALUES(gym),
                    near_school = VALUES(near_school),
                    security = VALUES(security),
                    near_hospital = VALUES(near_hospital),
                    near_market = VALUES(near_market),
                    wifi = VALUES(wifi),
                    elevator = VALUES(elevator),
                    cctv = VALUES(cctv),
                    water_source = VALUES(water_source),
                    frontage_m = VALUES(frontage_m),
                    access_road_m = VALUES(access_road_m),
                    interior_level = VALUES(interior_level)");
        } else {
            $this->db->query("INSERT INTO property_amenity (
                    property_id, property_age, swimming_pool, parking, gym, near_school, security, near_hospital, near_market, wifi, elevator, cctv, water_source
                ) VALUES (
                    :property_id, :property_age, :swimming_pool, :parking, :gym, :near_school, :security, :near_hospital, :near_market, :wifi, :elevator, :cctv, :water_source
                )
                ON DUPLICATE KEY UPDATE
                    property_age = VALUES(property_age),
                    swimming_pool = VALUES(swimming_pool),
                    parking = VALUES(parking),
                    gym = VALUES(gym),
                    near_school = VALUES(near_school),
                    security = VALUES(security),
                    near_hospital = VALUES(near_hospital),
                    near_market = VALUES(near_market),
                    wifi = VALUES(wifi),
                    elevator = VALUES(elevator),
                    cctv = VALUES(cctv),
                    water_source = VALUES(water_source)");
        }

        $this->db->bind(':property_id', (int) $propertyId);
        $this->db->bind(':property_age', $amenities['property_age']);
        $this->db->bind(':swimming_pool', (int) $amenities['swimming_pool']);
        $this->db->bind(':parking', (int) $amenities['parking']);
        $this->db->bind(':gym', (int) $amenities['gym']);
        $this->db->bind(':near_school', (int) $amenities['near_school']);
        $this->db->bind(':security', (int) $amenities['security']);
        $this->db->bind(':near_hospital', (int) $amenities['near_hospital']);
        $this->db->bind(':near_market', (int) $amenities['near_market']);
        $this->db->bind(':wifi', (int) $amenities['wifi']);
        $this->db->bind(':elevator', (int) $amenities['elevator']);
        $this->db->bind(':cctv', (int) $amenities['cctv']);
        $this->db->bind(':water_source', $amenities['water_source'] !== '' ? $amenities['water_source'] : null);

        if ($this->hasColumn('property_amenity', 'frontage_m')) {
            $this->db->bind(':frontage_m', $amenities['frontage_m'] !== null ? $amenities['frontage_m'] : null);
            $this->db->bind(':access_road_m', $amenities['access_road_m'] !== null ? $amenities['access_road_m'] : null);
            $this->db->bind(':interior_level', $amenities['interior_level'] !== '' ? $amenities['interior_level'] : null);
        }

        return $this->db->execute();
    }

    private function deletePropertyAmenities($propertyId) {
        if (!$this->hasTable('property_amenity')) {
            return;
        }

        try {
            $this->db->query("DELETE FROM property_amenity WHERE property_id = :property_id");
            $this->db->bind(':property_id', (int) $propertyId);
            $this->db->execute();
        } catch (Throwable $e) {
            // Ignore cleanup issues to avoid blocking property deletion.
        }
    }

    public function adminAddProperty($data, $images) {
        $location = $this->normalizeLocationPayload($data);
        $locationSupport = $this->getPropertyLocationColumnSupport();
        $typePayload = $this->resolvePropertyTypePayload($data);
        $amenities = $this->normalizePropertyAmenityPayload($data);
        $uploadDir = '../admin/property/';
        $imageFields = ['pimage','pimage1','pimage2','pimage3','pimage4','mapimage','topmapimage','groundmapimage'];
        $imgNames = [];
        
        foreach($imageFields as $field) {
            $fileKey = ($field == 'pimage') ? 'aimage' 
                     : (($field == 'mapimage') ? 'fimage' 
                     : (($field == 'topmapimage') ? 'fimage1' 
                     : (($field == 'groundmapimage') ? 'fimage2' : str_replace('p', 'a', $field))));

            if(!empty($images[$fileKey]['name'])) {
                $fname = time() . '_' . $field . '_' . basename($images[$fileKey]['name']);
                move_uploaded_file($images[$fileKey]['tmp_name'], $uploadDir . $fname);
                $imgNames[$field] = $fname;
            } else {
                $imgNames[$field] = '';
            }
        }

        $columns = [
            'title','pcontent','type','direction','stype','bedroom','bathroom','balcony','kitchen','hall','floor','size','price','location'
        ];
        $placeholders = [
            ':title',':content',':type',':direction',':stype',':bed',':bath',':balc',':kitc',':hall',':floor',':size',':price',':loc'
        ];
        if ($this->hasColumn('property', 'type_id')) {
            $columns[] = 'type_id';
            $placeholders[] = ':type_id';
        }
        $this->appendLocationColumnsForInsert($columns, $placeholders, $locationSupport);

        if ($this->hasLegacyFeatureColumn()) {
            $columns[] = 'feature';
            $placeholders[] = ':feature';
        }
        $columns = array_merge($columns, [
            'pimage','pimage1','pimage2','pimage3','pimage4','uid','status','approval_status','approval_seen','reviewed_at',
            'mapimage','topmapimage','groundmapimage','totalfloor','isFeatured'
        ]);
        $placeholders = array_merge($placeholders, [
            ':pimage',':pimage1',':pimage2',':pimage3',':pimage4',':uid',':status',':approval_status',':approval_seen',':reviewed_at',
            ':mapimage',':topmapimage',':groundmapimage',':totalfloor',':isFeatured'
        ]);

        $this->db->query("INSERT INTO property (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")");

        $this->db->bind(':title',        $data['title']);
        $this->db->bind(':content',      $data['content']);
        $this->db->bind(':type',         $typePayload['name']);
        if ($this->hasColumn('property', 'type_id')) {
            $this->db->bind(':type_id', $typePayload['id']);
        }
        $this->db->bind(':direction', $data['direction']);
        $this->db->bind(':stype',        $data['stype']);
        $this->db->bind(':bed',          $data['bed']);
        $this->db->bind(':bath',         $data['bath']);
        $this->db->bind(':balc',         $data['balc']);
        $this->db->bind(':kitc',         $data['kitc']);
        $this->db->bind(':hall',         $data['hall']);
        $this->db->bind(':floor',        $data['floor']);
        $this->db->bind(':size',         $data['asize']);
        $this->db->bind(':price',        $data['price']);
        $this->db->bind(':loc',          $data['loc']);
        $this->bindLocationValues($location, $locationSupport);
        if ($this->hasLegacyFeatureColumn()) {
            $this->db->bind(':feature', $this->buildFeatureHtmlFromAmenities($amenities));
        }
        $this->db->bind(':pimage',       $imgNames['pimage']);
        $this->db->bind(':pimage1',      $imgNames['pimage1']);
        $this->db->bind(':pimage2',      $imgNames['pimage2']);
        $this->db->bind(':pimage3',      $imgNames['pimage3']);
        $this->db->bind(':pimage4',      $imgNames['pimage4']);
        $this->db->bind(':uid',          $data['uid']);
        $this->db->bind(':status',       $data['status']);
        $this->db->bind(':approval_status', 'approved');
        $this->db->bind(':approval_seen', 1);
        $this->db->bind(':reviewed_at', date('Y-m-d H:i:s'));
        $this->db->bind(':mapimage',     $imgNames['mapimage']);
        $this->db->bind(':topmapimage',  $imgNames['topmapimage']);
        $this->db->bind(':groundmapimage', $imgNames['groundmapimage']);
        $this->db->bind(':totalfloor',   $data['totalfl']);
        $this->db->bind(':isFeatured',   $data['isFeatured']);

        if (!$this->db->execute()) {
            return false;
        }

        return $this->savePropertyAmenities((int) $this->db->lastInsertId(), $amenities);
    }

    public function adminUpdateProperty($id, $data, $images) {
        $location = $this->normalizeLocationPayload($data);
        $locationSupport = $this->getPropertyLocationColumnSupport();
        $typePayload = $this->resolvePropertyTypePayload($data);
        $amenities = $this->normalizePropertyAmenityPayload($data);
        $uploadDir = '../admin/property/';
        $oldProp = $this->getPropertyById($id);

        $data['floor'] = trim((string)($data['floor'] ?? ''));
        if (empty($data['floor'])) {
            $data['floor'] = isset($oldProp['floor']) && !empty($oldProp['floor']) ? $oldProp['floor'] : '';
        }

        $data['price'] = trim((string)($data['price'] ?? ''));
        if (empty($data['price']) || $data['price'] == '0') {
            $data['price'] = isset($oldProp['price']) && !empty($oldProp['price']) ? $oldProp['price'] : '';
        }

        $data['totalfl'] = trim((string)($data['totalfl'] ?? ''));
        if (empty($data['totalfl']) || $data['totalfl'] == '0') {
            $data['totalfl'] = isset($oldProp['totalfloor']) && !empty($oldProp['totalfloor']) ? $oldProp['totalfloor'] : '';
        }
        
        $imageFields = ['pimage','pimage1','pimage2','pimage3','pimage4','mapimage','topmapimage','groundmapimage'];
        $imgNames = [];
        
        foreach($imageFields as $field) {
             $fileKey = ($field == 'pimage') ? 'aimage' 
                     : (($field == 'mapimage') ? 'fimage' 
                     : (($field == 'topmapimage') ? 'fimage1' 
                     : (($field == 'groundmapimage') ? 'fimage2' : str_replace('p', 'a', $field))));

            if(!empty($images[$fileKey]['name'])) {
                $fname = time() . '_' . $field . '_' . basename($images[$fileKey]['name']);
                move_uploaded_file($images[$fileKey]['tmp_name'], $uploadDir . $fname);
                $imgNames[$field] = $fname;
                // Có thể xóa ảnh cũ ở đây nếu muốn
            } else {
                $imgNames[$field] = $oldProp[$field];
            }
        }

        $setParts = [
            'title=:title', 'pcontent=:content', 'type=:type', 'direction=:direction', 'stype=:stype',
            'bedroom=:bed', 'bathroom=:bath', 'balcony=:balc', 'kitchen=:kitc', 'hall=:hall',
            'floor=:floor', 'size=:size', 'price=:price', 'location=:loc'
        ];
        if ($this->hasColumn('property', 'type_id')) {
            $setParts[] = 'type_id=:type_id';
        }
        $this->appendLocationAssignmentsForUpdate($setParts, $locationSupport);
        if ($this->hasLegacyFeatureColumn()) {
            $setParts[] = 'feature=:feature';
        }
        $setParts = array_merge($setParts, [
            'pimage=:pimage', 'pimage1=:pimage1', 'pimage2=:pimage2', 'pimage3=:pimage3', 'pimage4=:pimage4',
            'uid=:uid', 'status=:status', 'approval_status=:approval_status', 'approval_seen=:approval_seen', 'reviewed_at=:reviewed_at',
            'mapimage=:mapimage', 'topmapimage=:topmapimage', 'groundmapimage=:groundmapimage', 'totalfloor=:totalfloor', 'isFeatured=:isFeatured'
        ]);
        $this->db->query("UPDATE property SET " . implode(', ', $setParts) . " WHERE pid = :pid");

        $this->db->bind(':title',        $data['title']);
        $this->db->bind(':content',      $data['content']);
        $this->db->bind(':type',         $typePayload['name']);
        if ($this->hasColumn('property', 'type_id')) {
            $this->db->bind(':type_id', $typePayload['id']);
        }
        $this->db->bind(':direction', $data['direction']);
        $this->db->bind(':stype',        $data['stype']);
        $this->db->bind(':bed',          $data['bed']);
        $this->db->bind(':bath',         $data['bath']);
        $this->db->bind(':balc',         $data['balc']);
        $this->db->bind(':kitc',         $data['kitc']);
        $this->db->bind(':hall',         $data['hall']);
        $this->db->bind(':floor',        $data['floor']);
        $this->db->bind(':size',         $data['asize']);
        // Final price safety check for admin: ensure price never becomes empty if old property had value
        if (empty($data['price']) && isset($oldProp['price']) && !empty($oldProp['price'])) {
            $data['price'] = $oldProp['price'];
        }
        $this->db->bind(':price',        $data['price']);
        $this->db->bind(':loc',          $data['loc']);
        $this->bindLocationValues($location, $locationSupport);
        if ($this->hasLegacyFeatureColumn()) {
            $this->db->bind(':feature', $this->buildFeatureHtmlFromAmenities($amenities));
        }
        $this->db->bind(':pimage',       $imgNames['pimage']);
        $this->db->bind(':pimage1',      $imgNames['pimage1']);
        $this->db->bind(':pimage2',      $imgNames['pimage2']);
        $this->db->bind(':pimage3',      $imgNames['pimage3']);
        $this->db->bind(':pimage4',      $imgNames['pimage4']);
        $this->db->bind(':uid',          $data['uid']);
        $this->db->bind(':status',       $data['status']);
        $this->db->bind(':approval_status', isset($oldProp['approval_status']) ? $oldProp['approval_status'] : 'approved');
        $this->db->bind(':approval_seen', isset($oldProp['approval_seen']) ? $oldProp['approval_seen'] : 1);
        $this->db->bind(':reviewed_at', isset($oldProp['reviewed_at']) ? $oldProp['reviewed_at'] : date('Y-m-d H:i:s'));
        $this->db->bind(':mapimage',     $imgNames['mapimage']);
        $this->db->bind(':topmapimage',  $imgNames['topmapimage']);
        $this->db->bind(':groundmapimage', $imgNames['groundmapimage']);
        $this->db->bind(':totalfloor',   $data['totalfl']);
        $this->db->bind(':isFeatured',   $data['isFeatured']);
        $this->db->bind(':pid',          $id);

        if (!$this->db->execute()) {
            return false;
        }

        return $this->savePropertyAmenities((int) $id, $amenities);
    }

    public function getRecentProperties() {
        $this->db->query("SELECT property.*, user.uname,user.utype,user.uimage FROM `property`, `user` WHERE property.uid=user.uid AND " . $this->publicApprovalWhere() . " ORDER BY date DESC LIMIT 9");
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function getAllProperties() {
        $this->db->query("SELECT property.*, user.uname, user.utype, user.uimage, user.uphone FROM `property`, `user` WHERE property.uid=user.uid AND " . $this->publicApprovalWhere() . " ORDER BY property.date DESC");
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function getPropertiesByFilter($type, $stype, $cityId = 0, $advanced = []) {
        $sql = "SELECT property.*, user.uname, user.utype, user.uimage, user.uphone 
                FROM `property` 
                LEFT JOIN `user` ON property.uid = user.uid ";

        $useTypeRelation = $this->canUsePropertyTypeRelation();
        if ($useTypeRelation) {
            $sql .= " LEFT JOIN `property_type` pt ON property.type_id = pt.id ";
        }

        $sql .= " WHERE " . $this->publicApprovalWhere();
        
        $binds = [];
        $keyword = isset($advanced['keyword']) ? trim((string) $advanced['keyword']) : '';
        $priceRange = isset($advanced['price_range']) ? trim((string) $advanced['price_range']) : '';
        $areaRange = isset($advanced['area_range']) ? trim((string) $advanced['area_range']) : '';
        $rooms = isset($advanced['rooms']) ? (int) $advanced['rooms'] : 0;
        $types = isset($advanced['types']) && is_array($advanced['types'])
            ? array_values(array_unique(array_filter(array_map('trim', $advanced['types']))))
            : [];
        
        if (empty($types) && !empty($type)) {
            $types = [$type];
        }

        if (!empty($types)) {
            $typeConds = [];
            foreach ($types as $index => $typeName) {
                $propertyTypeParam = ':ptype_' . $index;
                $binds[$propertyTypeParam] = $typeName;

                if ($useTypeRelation) {
                    $ptTypeParam = ':ptype_rel_' . $index;
                    $binds[$ptTypeParam] = $typeName;
                    $typeConds[] = "(property.type = {$propertyTypeParam} OR pt.name = {$ptTypeParam})";
                } else {
                    $typeConds[] = "property.type = {$propertyTypeParam}";
                }
            }
            $sql .= " AND (" . implode(' OR ', $typeConds) . ")";
        }
        if(!empty($stype)) {
            $sql .= " AND property.stype = :stype";
            $binds[':stype'] = $stype;
        }
        if(!empty($cityId)) {
            $sql .= " AND property.city_id = :city_id";
            $binds[':city_id'] = (int) $cityId;
        }

        if ($keyword !== '') {
            $keywordConds = [
                "property.title LIKE :keyword",
                "property.location LIKE :keyword",
            ];
            if ($this->hasColumn('property', 'city')) {
                $keywordConds[] = "property.city LIKE :keyword";
            }
            if ($this->hasColumn('property', 'state')) {
                $keywordConds[] = "property.state LIKE :keyword";
            }
            $sql .= " AND (" . implode(' OR ', $keywordConds) . ")";
            $binds[':keyword'] = '%' . $keyword . '%';
        }

        if ($priceRange !== '') {
            switch ($priceRange) {
                case 'lt_1m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) < 1";
                    break;
                case '1_3m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) BETWEEN 1 AND 3";
                    break;
                case '3_5m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) BETWEEN 3 AND 5";
                    break;
                case '5_10m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) BETWEEN 5 AND 10";
                    break;
                case '10_40m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) BETWEEN 10 AND 40";
                    break;
                case '40_70m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) BETWEEN 40 AND 70";
                    break;
                case '70_100m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) BETWEEN 70 AND 100";
                    break;
                case 'gt_100m':
                    $sql .= " AND CAST(property.price AS DECIMAL(12,2)) > 100";
                    break;
            }
        }

        if ($areaRange !== '') {
            switch ($areaRange) {
                case 'lt_30':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) < 30";
                    break;
                case '30_50':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 30 AND 50";
                    break;
                case '50_80':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 50 AND 80";
                    break;
                case '80_100':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 80 AND 100";
                    break;
                case '100_150':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 100 AND 150";
                    break;
                case '150_200':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 150 AND 200";
                    break;
                case '200_250':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 200 AND 250";
                    break;
                case '250_300':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 250 AND 300";
                    break;
                case '300_500':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) BETWEEN 300 AND 500";
                    break;
                case 'gt_500':
                    $sql .= " AND CAST(property.size AS DECIMAL(12,2)) > 500";
                    break;
            }
        }

        if ($rooms > 0) {
            $sql .= " AND CAST(property.bedroom AS UNSIGNED) >= :rooms";
            $binds[':rooms'] = $rooms;
        }
        
        $sql .= " ORDER BY property.date DESC";
        
        $this->db->query($sql);
        foreach($binds as $param => $value) {
            $this->db->bind($param, $value);
        }
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function getFeaturedProperties($limit = 3) {
        $this->db->query("SELECT * FROM `property` WHERE isFeatured = 1 AND approval_status = 'approved' ORDER BY date DESC LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function getRecentPropertiesLimit($limit = 6) {
        $this->db->query("SELECT property.*, user.uname, user.utype, user.uimage, user.uphone
            FROM `property`
            LEFT JOIN `user` ON property.uid = user.uid
            WHERE property.approval_status = 'approved'
            ORDER BY property.date DESC
            LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function getFavoritePropertyIds($uid) {
        $this->db->query("SELECT pid FROM property_favorite WHERE uid = :uid");
        $this->db->bind(':uid', $uid);
        $rows = $this->db->resultSet();

        if (empty($rows)) {
            return [];
        }

        return array_map('intval', array_column($rows, 'pid'));
    }

    public function isPropertyFavorited($uid, $pid) {
        $this->db->query("SELECT id FROM property_favorite WHERE uid = :uid AND pid = :pid LIMIT 1");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':pid', $pid);
        $row = $this->db->single();
        return !empty($row);
    }

    public function addFavorite($uid, $pid) {
        $this->db->query("INSERT INTO property_favorite (uid, pid, created_at) VALUES (:uid, :pid, :created_at)");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':pid', $pid);
        $this->db->bind(':created_at', date('Y-m-d H:i:s'));
        return $this->db->execute();
    }

    public function removeFavorite($uid, $pid) {
        $this->db->query("DELETE FROM property_favorite WHERE uid = :uid AND pid = :pid");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':pid', $pid);
        return $this->db->execute();
    }

    public function toggleFavorite($uid, $pid) {
        if ($this->isPropertyFavorited($uid, $pid)) {
            return $this->removeFavorite($uid, $pid) ? 'removed' : false;
        }

        return $this->addFavorite($uid, $pid) ? 'added' : false;
    }

    public function countFavorites($uid) {
        $this->db->query("SELECT COUNT(*) AS total FROM property_favorite pf
            INNER JOIN property ON pf.pid = property.pid
            WHERE pf.uid = :uid AND property.approval_status = 'approved'");
        $this->db->bind(':uid', $uid);
        $row = $this->db->single();
        return $row ? (int) $row['total'] : 0;
    }

    public function getRecentFavorites($uid, $limit = 3) {
        $this->db->query("SELECT pf.created_at AS favorite_created_at, property.pid, property.title, property.location, property.pimage
            FROM property_favorite pf
            INNER JOIN property ON pf.pid = property.pid
            WHERE pf.uid = :uid AND property.approval_status = 'approved'
            ORDER BY pf.created_at DESC
            LIMIT :limit");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getFavoriteProperties($uid) {
        $this->db->query("SELECT property.*, user.uname, user.utype, user.uimage, user.uphone, pf.created_at AS favorite_created_at
            FROM property_favorite pf
            INNER JOIN property ON pf.pid = property.pid
            LEFT JOIN user ON property.uid = user.uid
            WHERE pf.uid = :uid AND property.approval_status = 'approved'
            ORDER BY pf.created_at DESC");
        $this->db->bind(':uid', $uid);
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function addPropertyInquiry($data) {
        $columns = ['property_id', 'agent_uid', 'inquirer_uid', 'inquirer_name', 'work_email', 'phone', 'requirement'];
        $placeholders = [':property_id', ':agent_uid', ':inquirer_uid', ':inquirer_name', ':work_email', ':phone', ':requirement'];

        if ($this->hasColumn('property_inquiry', 'status')) {
            $columns[] = 'status';
            $placeholders[] = ':status';
        }
        if ($this->hasColumn('property_inquiry', 'case_status')) {
            $columns[] = 'case_status';
            $placeholders[] = ':case_status';
        }
        if ($this->hasColumn('property_inquiry', 'appointment_status')) {
            $columns[] = 'appointment_status';
            $placeholders[] = ':appointment_status';
        }

        if ($this->hasColumn('property_inquiry', 'desired_budget')) {
            $columns[] = 'desired_budget';
            $placeholders[] = ':desired_budget';
        }
        if ($this->hasColumn('property_inquiry', 'desired_area')) {
            $columns[] = 'desired_area';
            $placeholders[] = ':desired_area';
        }
        if ($this->hasColumn('property_inquiry', 'desired_move_in_time')) {
            $columns[] = 'desired_move_in_time';
            $placeholders[] = ':desired_move_in_time';
        }
        if ($this->hasColumn('property_inquiry', 'workflow_updated_at')) {
            $columns[] = 'workflow_updated_at';
            $placeholders[] = ':workflow_updated_at';
        }

        $this->db->query("INSERT INTO property_inquiry (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")");

        $this->db->bind(':property_id', (int) $data['property_id']);
        $this->db->bind(':agent_uid', (int) $data['agent_uid']);
        $this->db->bind(':inquirer_uid', !empty($data['inquirer_uid']) ? (int) $data['inquirer_uid'] : null);
        $this->db->bind(':inquirer_name', trim((string) $data['name']));
        $this->db->bind(':work_email', trim((string) $data['work_email']));
        $this->db->bind(':phone', trim((string) $data['phone']));
        $this->db->bind(':requirement', trim((string) $data['requirement']));

        if ($this->hasColumn('property_inquiry', 'status')) {
            $this->db->bind(':status', 'pending');
        }
        if ($this->hasColumn('property_inquiry', 'case_status')) {
            $this->db->bind(':case_status', 'new');
        }
        if ($this->hasColumn('property_inquiry', 'appointment_status')) {
            $this->db->bind(':appointment_status', 'none');
        }

        if ($this->hasColumn('property_inquiry', 'desired_budget')) {
            $this->db->bind(':desired_budget', isset($data['desired_budget']) ? trim((string) $data['desired_budget']) : '');
        }
        if ($this->hasColumn('property_inquiry', 'desired_area')) {
            $this->db->bind(':desired_area', isset($data['desired_area']) ? trim((string) $data['desired_area']) : '');
        }
        if ($this->hasColumn('property_inquiry', 'desired_move_in_time')) {
            $this->db->bind(':desired_move_in_time', isset($data['desired_move_in_time']) ? trim((string) $data['desired_move_in_time']) : '');
        }
        if ($this->hasColumn('property_inquiry', 'workflow_updated_at')) {
            $this->db->bind(':workflow_updated_at', date('Y-m-d H:i:s'));
        }

        return $this->db->execute();
    }

    public function getInquiryWorkflowOptions() {
        return [
            'status' => ['pending', 'accepted', 'rejected'],
            'case_status' => ['new', 'contacted', 'scheduled', 'viewed', 'completed', 'cancelled'],
            'appointment_status' => ['none', 'pending', 'confirmed', 'completed', 'cancelled'],
        ];
    }

    private function getWorkflowEnumOptionsByField($field) {
        $options = $this->getInquiryWorkflowOptions();
        return isset($options[$field]) && is_array($options[$field]) ? $options[$field] : [];
    }

    private function normalizeInquiryFieldValue($field, $value) {
        $enumFields = ['status', 'case_status', 'appointment_status'];
        if (in_array($field, $enumFields, true)) {
            $allowed = $this->getWorkflowEnumOptionsByField($field);
            return in_array($value, $allowed, true) ? $value : null;
        }

        if (in_array($field, ['appointment_requested_at', 'appointment_confirmed_at', 'viewed_at', 'contacted_at', 'workflow_updated_at'], true)) {
            return $value === null || trim((string) $value) === '' ? null : (string) $value;
        }

        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }

    private function autoSyncInquiryWorkflow(&$payload, $before = []) {
        $status = isset($payload['status']) ? $payload['status'] : ($before['status'] ?? null);
        $caseStatus = isset($payload['case_status']) ? $payload['case_status'] : ($before['case_status'] ?? null);
        $appointmentStatus = isset($payload['appointment_status']) ? $payload['appointment_status'] : ($before['appointment_status'] ?? null);

        if ($status === 'pending') {
            $payload['case_status'] = 'new';
        }

        if ($status === 'accepted' && !in_array($caseStatus, ['viewed', 'completed', 'cancelled'], true)) {
            if ($caseStatus === null || $caseStatus === '' || $caseStatus === 'new') {
                $payload['case_status'] = 'contacted';
            }
        }

        if (in_array($appointmentStatus, ['pending', 'confirmed'], true) && !in_array($caseStatus, ['viewed', 'completed', 'cancelled'], true)) {
            $payload['case_status'] = 'scheduled';
        }

        if (($appointmentStatus === 'completed' || !empty($payload['viewed_at'])) && !in_array($caseStatus, ['completed', 'cancelled'], true)) {
            $payload['case_status'] = 'viewed';
        }

        if ($caseStatus === 'completed' || (isset($payload['case_status']) && $payload['case_status'] === 'completed')) {
            $payload['status'] = 'accepted';
        }

        if ($status === 'rejected' || $caseStatus === 'cancelled' || (isset($payload['case_status']) && $payload['case_status'] === 'cancelled')) {
            $payload['status'] = 'rejected';
            if ($this->hasColumn('property_inquiry', 'appointment_status')) {
                $payload['appointment_status'] = 'cancelled';
            }
            $payload['case_status'] = 'cancelled';
        }
    }

    private function normalizeInquiryActorType($actorType) {
        $actorType = strtolower(trim((string) $actorType));
        $allowed = ['admin', 'agent', 'owner', 'user', 'system'];
        return in_array($actorType, $allowed, true) ? $actorType : 'system';
    }

    private function getAllowedInquiryUpdateFields() {
        return [
            'status',
            'notes',
            'contacted_at',
            'case_status',
            'desired_budget',
            'desired_area',
            'desired_move_in_time',
            'appointment_status',
            'appointment_requested_at',
            'appointment_confirmed_at',
            'appointment_note',
            'viewed_at',
            'result_note',
            'workflow_updated_at',
        ];
    }

    private function addInquiryLogEntry($inquiryId, $field, $oldValue, $newValue, $actor = []) {
        if (!$this->hasTable('property_inquiry_log')) {
            return;
        }

        if ((string) $oldValue === (string) $newValue) {
            return;
        }

        $labels = [
            'status' => 'Cap nhat trang thai tiep nhan',
            'case_status' => 'Cap nhat tien trinh xu ly',
            'appointment_status' => 'Cap nhat trang thai lich hen',
            'appointment_requested_at' => 'Cap nhat thoi gian de xuat',
            'appointment_confirmed_at' => 'Cap nhat thoi gian xac nhan',
            'viewed_at' => 'Danh dau da xem nha',
            'result_note' => 'Cap nhat ket qua',
        ];

        $actionLabel = isset($labels[$field]) ? $labels[$field] : ('Cap nhat ' . $field);

        $this->db->query("INSERT INTO property_inquiry_log (
            inquiry_id, action_key, action_label, old_value, new_value, actor_type, actor_id, actor_name
        ) VALUES (
            :inquiry_id, :action_key, :action_label, :old_value, :new_value, :actor_type, :actor_id, :actor_name
        )");
        $this->db->bind(':inquiry_id', (int) $inquiryId);
        $this->db->bind(':action_key', $field);
        $this->db->bind(':action_label', $actionLabel);
        $this->db->bind(':old_value', $oldValue === null ? null : (string) $oldValue);
        $this->db->bind(':new_value', $newValue === null ? null : (string) $newValue);
        $this->db->bind(':actor_type', $this->normalizeInquiryActorType($actor['actor_type'] ?? 'system'));
        $this->db->bind(':actor_id', isset($actor['actor_id']) ? (int) $actor['actor_id'] : null);
        $this->db->bind(':actor_name', isset($actor['actor_name']) ? (string) $actor['actor_name'] : null);
        $this->db->execute();
    }

    public function updateInquiryWorkflow($id, $data, $actor = []) {
        $id = (int) $id;
        if ($id <= 0 || empty($data) || !is_array($data)) {
            return false;
        }

        $before = $this->getInquiryById($id);
        if (empty($before)) {
            return false;
        }

        $allowedFields = $this->getAllowedInquiryUpdateFields();
        $updateFields = [];
        $normalizedPayload = [];

        foreach ($data as $field => $value) {
            if (!in_array($field, $allowedFields, true)) {
                continue;
            }
            if (!$this->hasColumn('property_inquiry', $field)) {
                continue;
            }

            $normalized = $this->normalizeInquiryFieldValue($field, $value);
            if ($normalized === null && in_array($field, ['status', 'case_status', 'appointment_status'], true)) {
                continue;
            }

            $updateFields[] = $field . ' = :' . $field;
            $normalizedPayload[$field] = $normalized;
        }

        $this->autoSyncInquiryWorkflow($normalizedPayload, $before);

        $updateFields = [];
        foreach ($normalizedPayload as $field => $value) {
            if (!in_array($field, $allowedFields, true)) {
                continue;
            }
            if (!$this->hasColumn('property_inquiry', $field)) {
                continue;
            }
            $updateFields[] = $field . ' = :' . $field;
        }

        if ($this->hasColumn('property_inquiry', 'workflow_updated_at') && !isset($normalizedPayload['workflow_updated_at'])) {
            $updateFields[] = 'workflow_updated_at = :workflow_updated_at';
            $normalizedPayload['workflow_updated_at'] = date('Y-m-d H:i:s');
        }

        if (empty($updateFields)) {
            return false;
        }

        $sql = "UPDATE property_inquiry SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        foreach ($normalizedPayload as $field => $value) {
            $this->db->bind(':' . $field, $value);
        }

        $updated = $this->db->execute();
        if (!$updated) {
            return false;
        }

        $afterStatus = $normalizedPayload['case_status'] ?? ($before['case_status'] ?? null);
        $propertyId = isset($before['property_id']) ? (int) $before['property_id'] : 0;
        if ($propertyId > 0 && $afterStatus !== null) {
            $this->syncPropertyStatusByInquiryOutcome($propertyId, (string) $afterStatus);
        }

        foreach ($normalizedPayload as $field => $value) {
            if ($field === 'workflow_updated_at') {
                continue;
            }
            $oldValue = isset($before[$field]) ? $before[$field] : null;
            $this->addInquiryLogEntry($id, $field, $oldValue, $value, $actor);
        }

        return true;
    }

    public function getInquiryLogs($id, $limit = 100) {
        if (!$this->hasTable('property_inquiry_log')) {
            return [];
        }

        $this->db->query("SELECT * FROM property_inquiry_log WHERE inquiry_id = :inquiry_id ORDER BY created_at DESC, id DESC LIMIT :limit");
        $this->db->bind(':inquiry_id', (int) $id);
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getInquiryWorkflowSummary() {
        $summary = [
            'total' => $this->countInquiries([]),
            'status' => [],
            'case_status' => [],
        ];

        $groupFields = ['status', 'case_status'];
        foreach ($groupFields as $field) {
            if (!$this->hasColumn('property_inquiry', $field)) {
                continue;
            }

            $this->db->query("SELECT {$field} AS value, COUNT(*) AS total FROM property_inquiry GROUP BY {$field}");
            $rows = $this->db->resultSet();
            foreach ($rows as $row) {
                $value = isset($row['value']) ? (string) $row['value'] : '';
                if ($value === '') {
                    $value = 'none';
                }
                $summary[$field][$value] = (int) ($row['total'] ?? 0);
            }
        }

        return $summary;
    }

    private function syncPropertyStatusByInquiryOutcome($propertyId, $caseStatus) {
        if (!$this->hasColumn('property', 'status')) {
            return;
        }

        $nextStatus = null;
        if ($caseStatus === 'completed') {
            $nextStatus = 'rented';
        } elseif (in_array($caseStatus, ['contacted', 'scheduled', 'viewed'], true)) {
            $nextStatus = 'in_progress';
        } elseif ($caseStatus === 'cancelled') {
            $this->db->query("SELECT COUNT(*) AS total
                FROM property_inquiry
                WHERE property_id = :property_id
                  AND case_status IN ('contacted','scheduled','viewed')
                  AND status = 'accepted'");
            $this->db->bind(':property_id', $propertyId);
            $row = $this->db->single();
            $nextStatus = (!empty($row) && (int) $row['total'] > 0) ? 'in_progress' : 'available';
        }

        if ($nextStatus === null) {
            return;
        }

        $this->db->query("UPDATE property SET status = :status WHERE pid = :pid");
        $this->db->bind(':status', $nextStatus);
        $this->db->bind(':pid', $propertyId);
        $this->db->execute();
    }

    public function trackOwnerCallClick($propertyId, $ownerUid, $callerUid = null, $callerIp = null, $callerUserAgent = null) {
        if (!$this->hasTable('property_owner_call_click')) {
            return false;
        }

        $this->db->query("INSERT INTO property_owner_call_click (
            property_id, owner_uid, caller_uid, caller_ip, caller_user_agent, clicked_at
        ) VALUES (
            :property_id, :owner_uid, :caller_uid, :caller_ip, :caller_user_agent, :clicked_at
        )");

        $this->db->bind(':property_id', (int) $propertyId);
        $this->db->bind(':owner_uid', (int) $ownerUid);
        $this->db->bind(':caller_uid', $callerUid !== null ? (int) $callerUid : null);
        $this->db->bind(':caller_ip', $callerIp);
        $this->db->bind(':caller_user_agent', $callerUserAgent);
        $this->db->bind(':clicked_at', date('Y-m-d H:i:s'));

        return $this->db->execute();
    }

    public function getInquiriesByAgent($agentUid, $limit = 100) {
        $sql = "SELECT pi.*, property.title AS property_title, property.location AS property_location
            FROM property_inquiry pi
            LEFT JOIN property ON property.pid = pi.property_id
            WHERE pi.agent_uid = :agent_uid
            ORDER BY pi.created_at DESC
            LIMIT :limit";

        $this->db->query($sql);

        $this->db->bind(':agent_uid', (int) $agentUid);
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function getInquiryByIdForAgent($id, $agentUid) {
        $this->db->query("SELECT pi.*, property.title AS property_title, property.location AS property_location,
                         property.pimage AS property_image
                         FROM property_inquiry pi
                         LEFT JOIN property ON property.pid = pi.property_id
                         WHERE pi.id = :id AND pi.agent_uid = :agent_uid
                         LIMIT 1");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':agent_uid', (int) $agentUid);
        return $this->db->single();
    }

    public function getInquiriesByInquirer($inquirerUid, $limit = 100) {
        $this->db->query("SELECT pi.*, property.title AS property_title, property.location AS property_location,
                         property.price AS property_price, property.stype AS property_stype, property.pimage AS property_image,
                         user.uname AS agent_name, user.uphone AS agent_phone, user.uemail AS agent_email, user.uimage AS agent_image
                         FROM property_inquiry pi
                         LEFT JOIN property ON property.pid = pi.property_id
                         LEFT JOIN user ON user.uid = pi.agent_uid
                         WHERE pi.inquirer_uid = :inquirer_uid
                         ORDER BY pi.created_at DESC
                         LIMIT :limit");
        $this->db->bind(':inquirer_uid', (int) $inquirerUid);
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getInquiryByIdForInquirer($id, $inquirerUid) {
        $this->db->query("SELECT pi.*, property.title AS property_title, property.location AS property_location,
                         property.price AS property_price, property.stype AS property_stype, property.pimage AS property_image,
                         user.uname AS agent_name, user.uphone AS agent_phone, user.uemail AS agent_email, user.uimage AS agent_image
                         FROM property_inquiry pi
                         LEFT JOIN property ON property.pid = pi.property_id
                         LEFT JOIN user ON user.uid = pi.agent_uid
                         WHERE pi.id = :id AND pi.inquirer_uid = :inquirer_uid
                         LIMIT 1");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':inquirer_uid', (int) $inquirerUid);
        return $this->db->single();
    }

    public function getInquiryPeersByProperty($propertyId, $excludeInquiryId = null, $limit = 30) {
        $propertyId = (int) $propertyId;
        if ($propertyId <= 0) {
            return [];
        }

        $sql = "SELECT pi.id, pi.created_at, pi.inquirer_name, pi.work_email, pi.phone, pi.status, pi.case_status,
                       pi.appointment_status, pi.appointment_requested_at, pi.appointment_confirmed_at, pi.workflow_updated_at, pi.property_id,
                       property.title AS property_title
                FROM property_inquiry pi
                LEFT JOIN property ON property.pid = pi.property_id
                WHERE pi.property_id = :property_id";

        if ($excludeInquiryId !== null) {
            $sql .= " AND pi.id <> :exclude_id";
        }

        $sql .= " ORDER BY pi.created_at DESC LIMIT :limit";

        $this->db->query($sql);
        $this->db->bind(':property_id', $propertyId, PDO::PARAM_INT);
        if ($excludeInquiryId !== null) {
            $this->db->bind(':exclude_id', (int) $excludeInquiryId, PDO::PARAM_INT);
        }
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function getAgentBusyAppointmentSlots($agentUid, $excludeInquiryId = null) {
        if (!$this->hasColumn('property_inquiry', 'appointment_status')) {
            return [];
        }

        $agentUid = (int) $agentUid;
        if ($agentUid <= 0) {
            return [];
        }

        $sql = "SELECT COALESCE(appointment_confirmed_at, appointment_requested_at) AS slot
                FROM property_inquiry
                WHERE agent_uid = :agent_uid
                  AND appointment_status IN ('pending', 'confirmed')
                  AND COALESCE(appointment_confirmed_at, appointment_requested_at) IS NOT NULL";

        $params = [':agent_uid' => $agentUid];

        if ($excludeInquiryId !== null) {
            $sql .= " AND id <> :exclude_id";
            $params[':exclude_id'] = (int) $excludeInquiryId;
        }

        $sql .= " AND COALESCE(appointment_confirmed_at, appointment_requested_at) >= :now
                  ORDER BY COALESCE(appointment_confirmed_at, appointment_requested_at) ASC";
        $params[':now'] = date('Y-m-d H:i:s');

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $paramType = $key === ':agent_uid' || $key === ':exclude_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->db->bind($key, $value, $paramType);
        }

        $rows = $this->db->resultSet();
        if (empty($rows)) {
            return [];
        }

        $slots = [];
        foreach ($rows as $row) {
            $slot = isset($row['slot']) ? trim((string) $row['slot']) : '';
            if ($slot !== '') {
                $slots[] = $slot;
            }
        }

        return $slots;
    }

    public function hasAppointmentConflict($agentUid, $appointmentAt, $excludeInquiryId = null) {
        if (!$this->hasColumn('property_inquiry', 'appointment_status')) {
            return false;
        }

        $agentUid = (int) $agentUid;
        $appointmentAt = trim((string) $appointmentAt);
        if ($agentUid <= 0 || $appointmentAt === '') {
            return false;
        }

        $sql = "SELECT COUNT(id) AS total
                FROM property_inquiry
                WHERE agent_uid = :agent_uid
                  AND appointment_status IN ('pending', 'confirmed')
                  AND (
                        (appointment_requested_at IS NOT NULL AND appointment_requested_at = :slot)
                     OR (appointment_confirmed_at IS NOT NULL AND appointment_confirmed_at = :slot)
                  )";

        if ($excludeInquiryId !== null) {
            $sql .= " AND id <> :exclude_id";
        }

        $this->db->query($sql);
        $this->db->bind(':agent_uid', $agentUid, PDO::PARAM_INT);
        $this->db->bind(':slot', $appointmentAt);
        if ($excludeInquiryId !== null) {
            $this->db->bind(':exclude_id', (int) $excludeInquiryId, PDO::PARAM_INT);
        }

        $row = $this->db->single();
        return $row && (int) ($row['total'] ?? 0) > 0;
    }

    public function getPropertiesByUser($uid) {
        $this->db->query("SELECT * FROM property WHERE uid = :uid ORDER BY date DESC");
        $this->db->bind(':uid', $uid);
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function getApprovedPropertiesByUser($uid, $limit = null) {
        $sql = "SELECT property.*, user.uname, user.utype, user.uimage, user.uphone
            FROM property
            LEFT JOIN user ON property.uid = user.uid
            WHERE property.uid = :uid AND property.approval_status = 'approved'
            ORDER BY property.date DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $this->db->query($sql);
        $this->db->bind(':uid', $uid);
        if ($limit !== null) {
            $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
        }

        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function getAgentAreaLabels($uid, $limit = 3) {
        $properties = $this->getApprovedPropertiesByUser($uid);
        if (empty($properties)) {
            return [];
        }

        $labels = [];
        foreach ($properties as $property) {
            $parts = [];
            if (!empty($property['state'])) {
                $parts[] = trim((string) $property['state']);
            }
            if (!empty($property['city'])) {
                $parts[] = trim((string) $property['city']);
            }
            $label = implode(', ', array_filter($parts));
            if ($label === '') {
                continue;
            }
            $labels[$label] = true;
            if (count($labels) >= $limit) {
                break;
            }
        }

        return array_keys($labels);
    }

    public function countUnreadApprovalNotifications($uid) {
        $this->db->query("SELECT COUNT(pid) AS total FROM property WHERE uid = :uid AND approval_status IN ('approved', 'rejected') AND approval_seen = 0");
        $this->db->bind(':uid', $uid);
        $row = $this->db->single();
        return $row ? (int) $row['total'] : 0;
    }

    public function getRecentApprovalNotifications($uid, $limit = 5) {
        $this->db->query("SELECT pid, title, pimage, approval_status, reviewed_at FROM property WHERE uid = :uid AND approval_status IN ('approved', 'rejected') ORDER BY COALESCE(reviewed_at, date) DESC LIMIT :limit");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function markApprovalNotificationsSeen($uid) {
        $this->db->query("UPDATE property SET approval_seen = 1 WHERE uid = :uid AND approval_status IN ('approved', 'rejected') AND approval_seen = 0");
        $this->db->bind(':uid', $uid);
        return $this->db->execute();
    }

    public function updateApprovalStatus($pid, $status) {
        $allowedStatuses = ['approved', 'rejected', 'pending'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $this->db->query("UPDATE property SET approval_status = :approval_status, approval_seen = :approval_seen, reviewed_at = :reviewed_at WHERE pid = :pid");
        $this->db->bind(':approval_status', $status);
        $this->db->bind(':approval_seen', $status === 'pending' ? 1 : 0);
        $this->db->bind(':reviewed_at', date('Y-m-d H:i:s'));
        $this->db->bind(':pid', $pid);
        return $this->db->execute();
    }

    public function deleteProperty($pid, $uid) {
        $this->db->query("SELECT pimage, pimage1, pimage2, pimage3, pimage4, mapimage, topmapimage, groundmapimage FROM property WHERE pid = :pid AND uid = :uid");
        $this->db->bind(':pid', $pid);
        $this->db->bind(':uid', $uid);
        $images = $this->db->single();
        
        if($images) {
            $this->deletePropertyAmenities($pid);
            $this->db->query("DELETE FROM property WHERE pid = :pid AND uid = :uid");
            $this->db->bind(':pid', $pid);
            $this->db->bind(':uid', $uid);
            if ($this->db->execute()) {
                $uploadDir = "../admin/property/";
                foreach($images as $img) {
                    if (empty($img)) {
                        continue;
                    }

                    // Only remove physical files when no other record references them.
                    if ($this->countImageReferences($img) === 0 && file_exists($uploadDir . $img)) {
                        unlink($uploadDir . $img);
                    }
                }
                return true;
            }
        }
        return false;
    }

    private function deleteFavoriteReferences($pid) {
        $this->db->query("DELETE FROM property_favorite WHERE pid = :pid");
        $this->db->bind(':pid', $pid);
        $this->db->execute();
    }

    private function countImageReferences($imageName) {
        $this->db->query("SELECT COUNT(*) AS total FROM property
            WHERE pimage = :img OR pimage1 = :img OR pimage2 = :img OR pimage3 = :img OR pimage4 = :img
               OR mapimage = :img OR topmapimage = :img OR groundmapimage = :img");
        $this->db->bind(':img', $imageName);
        $row = $this->db->single();
        return $row ? (int) $row['total'] : 0;
    }

    public function updateProperty($pid, $uid, $data, $images) {
        $location = $this->normalizeLocationPayload($data);
        $locationSupport = $this->getPropertyLocationColumnSupport();
        $typePayload = $this->resolvePropertyTypePayload($data);
        $amenities = $this->normalizePropertyAmenityPayload($data);
        $oldProp = $this->getPropertyById($pid);

        $data['floor'] = trim((string)($data['floor'] ?? ''));
        if (empty($data['floor'])) {
            $data['floor'] = isset($oldProp['floor']) && !empty($oldProp['floor']) ? $oldProp['floor'] : '';
        }

        $data['price'] = trim((string)($data['price'] ?? ''));
        if (empty($data['price']) || $data['price'] == '0') {
            $data['price'] = isset($oldProp['price']) && !empty($oldProp['price']) ? $oldProp['price'] : '';
        }

        $data['totalfl'] = trim((string)($data['totalfl'] ?? ''));
        if (empty($data['totalfl']) || $data['totalfl'] == '0') {
            $data['totalfl'] = isset($oldProp['totalfloor']) && !empty($oldProp['totalfloor']) ? $oldProp['totalfloor'] : '';
        }

        $setParts = [
            'title=:title', 'pcontent=:content', 'type=:ptype', 'direction=:direction', 'stype=:stype',
            'bedroom=:bed', 'bathroom=:bath', 'balcony=:balc', 'kitchen=:kitc', 'hall=:hall', 'floor=:floor',
            'size=:asize', 'price=:price', 'location=:loc'
        ];
        if ($this->hasColumn('property', 'type_id')) {
            $setParts[] = 'type_id=:type_id';
        }
        $this->appendLocationAssignmentsForUpdate($setParts, $locationSupport);
        if ($this->hasLegacyFeatureColumn()) {
            $setParts[] = 'feature=:feature';
        }
        $setParts = array_merge($setParts, [
            'status=:status', 'approval_status=:approval_status', 'approval_seen=:approval_seen', 'reviewed_at=:reviewed_at',
            'totalfloor=:totalfl', 'isFeatured=:isFeatured'
        ]);
        $sql = "UPDATE property SET " . implode(', ', $setParts);
        
        if(!empty($images['aimage'])) $sql .= ", pimage=:aimage";
        if(!empty($images['aimage1'])) $sql .= ", pimage1=:aimage1";
        if(!empty($images['aimage2'])) $sql .= ", pimage2=:aimage2";
        if(!empty($images['aimage3'])) $sql .= ", pimage3=:aimage3";
        if(!empty($images['aimage4'])) $sql .= ", pimage4=:aimage4";
        if(!empty($images['fimage'])) $sql .= ", mapimage=:fimage";
        if(!empty($images['fimage1'])) $sql .= ", topmapimage=:fimage1";
        if(!empty($images['fimage2'])) $sql .= ", groundmapimage=:fimage2";

        $sql .= " WHERE pid = :pid AND uid = :uid";
        $this->db->query($sql);

        $this->db->bind(':pid', $pid);
        $this->db->bind(':uid', $uid);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':ptype', $typePayload['name']);
        if ($this->hasColumn('property', 'type_id')) {
            $this->db->bind(':type_id', $typePayload['id']);
        }
        $this->db->bind(':direction', $data['direction']);
        $this->db->bind(':stype', $data['stype']);
        $this->db->bind(':bed', $data['bed']);
        $this->db->bind(':bath', $data['bath']);
        $this->db->bind(':balc', $data['balc']);
        $this->db->bind(':kitc', $data['kitc']);
        $this->db->bind(':hall', $data['hall']);
        $this->db->bind(':floor', $data['floor']);
        $this->db->bind(':asize', $data['asize']);
        // Final price safety check for user: ensure price never becomes empty if old property had value
        if (empty($data['price']) && isset($oldProp['price']) && !empty($oldProp['price'])) {
            $data['price'] = $oldProp['price'];
        }
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':loc', $data['loc']);
        $this->bindLocationValues($location, $locationSupport);
        if ($this->hasLegacyFeatureColumn()) {
            $this->db->bind(':feature', $this->buildFeatureHtmlFromAmenities($amenities));
        }
        $this->db->bind(':status',  $data['status']);
        $this->db->bind(':approval_status', 'pending');
        $this->db->bind(':approval_seen', 1);
        $this->db->bind(':reviewed_at', null);
        $this->db->bind(':totalfl', $data['totalfl']);
        $this->db->bind(':isFeatured', $data['isFeatured']);

        if(!empty($images['aimage'])) $this->db->bind(':aimage', $images['aimage']);
        if(!empty($images['aimage1'])) $this->db->bind(':aimage1', $images['aimage1']);
        if(!empty($images['aimage2'])) $this->db->bind(':aimage2', $images['aimage2']);
        if(!empty($images['aimage3'])) $this->db->bind(':aimage3', $images['aimage3']);
        if(!empty($images['aimage4'])) $this->db->bind(':aimage4', $images['aimage4']);
        if(!empty($images['fimage'])) $this->db->bind(':fimage', $images['fimage']);
        if(!empty($images['fimage1'])) $this->db->bind(':fimage1', $images['fimage1']);
        if(!empty($images['fimage2'])) $this->db->bind(':fimage2', $images['fimage2']);
        
        if (!$this->db->execute()) {
            return false;
        }

        return $this->savePropertyAmenities((int) $pid, $amenities);
    }

    public function getPropertyStatusById($pid) {
        $this->db->query("SELECT status FROM property WHERE pid = :pid LIMIT 1");
        $this->db->bind(':pid', (int) $pid);
        $row = $this->db->single();
        return $row ? (string) $row['status'] : 'available';
    }

    public function getPropertyById($id) {
        $this->db->query("SELECT property.*, 
                         user.uid as uid, user.uname, user.uemail as uemail, 
                         user.uphone, user.utype, user.uimage as uimage
                         FROM `property`
                         LEFT JOIN `user` ON property.uid = user.uid 
                         WHERE pid = :id");
        $this->db->bind(':id', $id);
        return $this->attachAmenitiesToPropertyRow($this->hydrateLocationRow($this->db->single()));
    }

    public function getPublicPropertyById($id) {
        $this->db->query("SELECT property.*, 
                         user.uid as uid, user.uname, user.uemail as uemail, 
                         user.uphone, user.utype, user.uimage as uimage
                         FROM `property`
                         LEFT JOIN `user` ON property.uid = user.uid 
                         WHERE pid = :id AND property.approval_status = 'approved'");
        $this->db->bind(':id', $id);
        return $this->attachAmenitiesToPropertyRow($this->hydrateLocationRow($this->db->single()));
    }

    public function addProperty($data, $images) {
        $location = $this->normalizeLocationPayload($data);
        $locationSupport = $this->getPropertyLocationColumnSupport();
        $typePayload = $this->resolvePropertyTypePayload($data);
        $amenities = $this->normalizePropertyAmenityPayload($data);
        $columns = [
            'title','pcontent','type','direction','stype','bedroom','bathroom','balcony','kitchen','hall','floor','size','price','location'
        ];
        $placeholders = [
            ':title',':content',':ptype',':direction',':stype',':bed',':bath',':balc',':kitc',':hall',':floor',':asize',':price',':loc'
        ];
        if ($this->hasColumn('property', 'type_id')) {
            $columns[] = 'type_id';
            $placeholders[] = ':type_id';
        }
        $this->appendLocationColumnsForInsert($columns, $placeholders, $locationSupport);
        if ($this->hasLegacyFeatureColumn()) {
            $columns[] = 'feature';
            $placeholders[] = ':feature';
        }
        $columns = array_merge($columns, [
            'uid','status','approval_status','approval_seen','reviewed_at','totalfloor','isFeatured',
            'pimage','pimage1','pimage2','pimage3','pimage4','mapimage','topmapimage','groundmapimage'
        ]);
        $placeholders = array_merge($placeholders, [
            ':uid',':status',':approval_status',':approval_seen',':reviewed_at',':totalfl',':isFeatured',
            ':aimage',':aimage1',':aimage2',':aimage3',':aimage4',':fimage',':fimage1',':fimage2'
        ]);
        $sql = "INSERT INTO property (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $this->db->query($sql);
        
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':ptype', $typePayload['name']);
        if ($this->hasColumn('property', 'type_id')) {
            $this->db->bind(':type_id', $typePayload['id']);
        }
        $this->db->bind(':direction', $data['direction']);
        $this->db->bind(':stype', $data['stype']);
        $this->db->bind(':bed', $data['bed']);
        $this->db->bind(':bath', $data['bath']);
        $this->db->bind(':balc', $data['balc']);
        $this->db->bind(':kitc', $data['kitc']);
        $this->db->bind(':hall', $data['hall']);
        $this->db->bind(':floor', $data['floor']);
        $this->db->bind(':asize', $data['asize']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':loc', $data['loc']);
        $this->bindLocationValues($location, $locationSupport);
        if ($this->hasLegacyFeatureColumn()) {
            $this->db->bind(':feature', $this->buildFeatureHtmlFromAmenities($amenities));
        }
        $this->db->bind(':uid',     $data['uid']);
        $this->db->bind(':status',  $data['status']);
        $this->db->bind(':approval_status', 'pending');
        $this->db->bind(':approval_seen', 1);
        $this->db->bind(':reviewed_at', null);
        $this->db->bind(':totalfl', $data['totalfl']);
        $this->db->bind(':isFeatured', $data['isFeatured']);
        
        $this->db->bind(':aimage', $images['aimage']);
        $this->db->bind(':aimage1', $images['aimage1']);
        $this->db->bind(':aimage2', $images['aimage2']);
        $this->db->bind(':aimage3', $images['aimage3']);
        $this->db->bind(':aimage4', $images['aimage4']);
        $this->db->bind(':fimage', $images['fimage']);
        $this->db->bind(':fimage1', $images['fimage1']);
        $this->db->bind(':fimage2', $images['fimage2']);
        
        if (!$this->db->execute()) {
            return false;
        }

        return $this->savePropertyAmenities((int) $this->db->lastInsertId(), $amenities);
    }


    public function countProperties() {
        $this->db->query("SELECT count(pid) as total FROM property WHERE approval_status = 'approved'");
        $row = $this->db->single();
        return $row ? $row['total'] : 0;
    }

    public function countSaleProperties() {
        $this->db->query("SELECT count(pid) as total FROM property WHERE stype='sale' AND approval_status = 'approved'");
        $row = $this->db->single();
        return $row ? $row['total'] : 0;
    }

    public function countRentProperties() {
        $this->db->query("SELECT count(pid) as total FROM property WHERE stype='rent' AND approval_status = 'approved'");
        $row = $this->db->single();
        return $row ? $row['total'] : 0;
    }

    public function countPropertiesByCity($city) {
        if ($this->hasColumn('property', 'city')) {
            $this->db->query("SELECT count(pid) as total FROM property WHERE city=:city AND approval_status = 'approved'");
            $this->db->bind(':city', $city);
        } else {
            $this->db->query("SELECT COUNT(property.pid) AS total
                FROM property
                LEFT JOIN city ON property.city_id = city.cid
                WHERE city.cname = :city AND property.approval_status = 'approved'");
            $this->db->bind(':city', $city);
        }
        $row = $this->db->single();
        return $row ? $row['total'] : 0;
    }

    public function getAllPropertiesAdmin() {
        $this->db->query("SELECT property.*, user.uname FROM property LEFT JOIN user ON property.uid = user.uid ORDER BY property.date DESC");
        return $this->hydrateLocationRows($this->db->resultSet());
    }

    public function incrementViewCount($pid) {
        if ($this->hasColumn('property', 'view_count')) {
            $this->db->query("UPDATE property SET view_count = view_count + 1 WHERE pid = :pid");
            $this->db->bind(':pid', $pid);
            return $this->db->execute();
        }
        return false;
    }

    public function adminDeleteProperty($pid) {
        // Lấy ảnh để xóa file vật lý
        $this->db->query("SELECT pimage, pimage1, pimage2, pimage3, pimage4, mapimage, topmapimage, groundmapimage FROM property WHERE pid = :pid");
        $this->db->bind(':pid', $pid);
        $images = $this->db->single();

        if($images) {
            $this->deleteFavoriteReferences($pid);
            $this->deleteFavoriteReferences($pid);
            $this->deletePropertyAmenities($pid);
            $this->db->query("DELETE FROM property WHERE pid = :pid");
            $this->db->bind(':pid', $pid);
            if($this->db->execute()) {
                $uploadDir = "../admin/property/";
                foreach($images as $img) {
                    if(!empty($img) && $this->countImageReferences($img) === 0 && file_exists($uploadDir . $img)) {
                        @unlink($uploadDir . $img);
                    }
                }
                return true;
            }
        }
        return false;
    }

    // Inquiry Management Methods for Admin CRM
    public function getInquiryById($id) {
        $this->db->query("SELECT pi.*, property.title AS property_title, property.location AS property_location, 
                         user.uname AS agent_name, user.uemail AS agent_email, user.uphone AS agent_phone
                         FROM property_inquiry pi
                         LEFT JOIN property ON property.pid = pi.property_id
                         LEFT JOIN user ON user.uid = pi.agent_uid
                         WHERE pi.id = :id");
        $this->db->bind(':id', (int) $id);
        return $this->db->single();
    }

    public function getAllInquiries($filters = []) {
        $sql = "SELECT pi.*, property.title AS property_title, property.location AS property_location,
                user.uname AS agent_name, user.uemail AS agent_email, user.uphone AS agent_phone
                FROM property_inquiry pi
                LEFT JOIN property ON property.pid = pi.property_id
                LEFT JOIN user ON user.uid = pi.agent_uid
                WHERE 1=1";

        if (!empty($filters['status'])) {
            $sql .= " AND pi.status = :status";
        }

        if (!empty($filters['case_status']) && $this->hasColumn('property_inquiry', 'case_status')) {
            $sql .= " AND pi.case_status = :case_status";
        }

        if (!empty($filters['appointment_status']) && $this->hasColumn('property_inquiry', 'appointment_status')) {
            $sql .= " AND pi.appointment_status = :appointment_status";
        }

        if (!empty($filters['agent_uid'])) {
            $sql .= " AND pi.agent_uid = :agent_uid";
        }

        if (!empty($filters['property_id'])) {
            $sql .= " AND pi.property_id = :property_id";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (pi.inquirer_name LIKE :search OR pi.work_email LIKE :search OR pi.phone LIKE :search)";
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(pi.created_at) >= :date_from";
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(pi.created_at) <= :date_to";
        }

        // Whitelist sortable columns to avoid SQL injection via sort parameter.
        $allowedSortColumns = ['created_at', 'inquirer_name', 'status'];
        $sortBy = isset($filters['sort']) && in_array($filters['sort'], $allowedSortColumns, true)
            ? $filters['sort']
            : 'created_at';
        $sortOrder = isset($filters['order']) && strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY pi.{$sortBy} {$sortOrder}";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET :offset";
            }
        }

        $this->db->query($sql);

        if (!empty($filters['status'])) {
            $this->db->bind(':status', $filters['status']);
        }
        if (!empty($filters['case_status']) && $this->hasColumn('property_inquiry', 'case_status')) {
            $this->db->bind(':case_status', $filters['case_status']);
        }
        if (!empty($filters['appointment_status']) && $this->hasColumn('property_inquiry', 'appointment_status')) {
            $this->db->bind(':appointment_status', $filters['appointment_status']);
        }
        if (!empty($filters['agent_uid'])) {
            $this->db->bind(':agent_uid', (int) $filters['agent_uid']);
        }
        if (!empty($filters['property_id'])) {
            $this->db->bind(':property_id', (int) $filters['property_id']);
        }
        if (!empty($filters['search'])) {
            $this->db->bind(':search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['date_from'])) {
            $this->db->bind(':date_from', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->bind(':date_to', $filters['date_to']);
        }
        if (!empty($filters['limit'])) {
            $this->db->bind(':limit', (int) $filters['limit'], PDO::PARAM_INT);
            if (!empty($filters['offset'])) {
                $this->db->bind(':offset', (int) $filters['offset'], PDO::PARAM_INT);
            }
        }

        return $this->db->resultSet();
    }

    public function updateInquiry($id, $data) {
        return $this->updateInquiryWorkflow($id, $data);
    }

    public function deleteInquiry($id) {
        $this->db->query("DELETE FROM property_inquiry WHERE id = :id");
        $this->db->bind(':id', (int) $id);
        return $this->db->execute();
    }

    public function countInquiries($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM property_inquiry pi WHERE 1=1";

        if (!empty($filters['status'])) {
            $sql .= " AND pi.status = :status";
        }
        if (!empty($filters['case_status']) && $this->hasColumn('property_inquiry', 'case_status')) {
            $sql .= " AND pi.case_status = :case_status";
        }
        if (!empty($filters['appointment_status']) && $this->hasColumn('property_inquiry', 'appointment_status')) {
            $sql .= " AND pi.appointment_status = :appointment_status";
        }
        if (!empty($filters['agent_uid'])) {
            $sql .= " AND pi.agent_uid = :agent_uid";
        }
        if (!empty($filters['property_id'])) {
            $sql .= " AND pi.property_id = :property_id";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (pi.inquirer_name LIKE :search OR pi.work_email LIKE :search OR pi.phone LIKE :search)";
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(pi.created_at) >= :date_from";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(pi.created_at) <= :date_to";
        }

        $this->db->query($sql);

        if (!empty($filters['status'])) {
            $this->db->bind(':status', $filters['status']);
        }
        if (!empty($filters['case_status']) && $this->hasColumn('property_inquiry', 'case_status')) {
            $this->db->bind(':case_status', $filters['case_status']);
        }
        if (!empty($filters['appointment_status']) && $this->hasColumn('property_inquiry', 'appointment_status')) {
            $this->db->bind(':appointment_status', $filters['appointment_status']);
        }
        if (!empty($filters['agent_uid'])) {
            $this->db->bind(':agent_uid', (int) $filters['agent_uid']);
        }
        if (!empty($filters['property_id'])) {
            $this->db->bind(':property_id', (int) $filters['property_id']);
        }
        if (!empty($filters['search'])) {
            $this->db->bind(':search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['date_from'])) {
            $this->db->bind(':date_from', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->bind(':date_to', $filters['date_to']);
        }

        $result = $this->db->single();
        return $result ? (int) $result['total'] : 0;
    }

    public function getAdminTransactionList($filters = []) {
        $queryFilters = [];
        if (!empty($filters['status'])) {
            $queryFilters['status'] = $filters['status'];
        }
        if (!empty($filters['case_status'])) {
            $queryFilters['case_status'] = $filters['case_status'];
        }
        if (!empty($filters['search'])) {
            $queryFilters['search'] = $filters['search'];
        }
        $queryFilters['sort'] = 'created_at';
        $queryFilters['order'] = 'DESC';
        if (!empty($filters['limit'])) {
            $queryFilters['limit'] = (int) $filters['limit'];
            $queryFilters['offset'] = !empty($filters['offset']) ? (int) $filters['offset'] : 0;
        }

        return $this->getAllInquiries($queryFilters);
    }

    public function countAdminTransactions($filters = []) {
        $queryFilters = [];
        if (!empty($filters['status'])) {
            $queryFilters['status'] = $filters['status'];
        }
        if (!empty($filters['case_status'])) {
            $queryFilters['case_status'] = $filters['case_status'];
        }
        if (!empty($filters['search'])) {
            $queryFilters['search'] = $filters['search'];
        }
        return $this->countInquiries($queryFilters);
    }

    public function getAdminAppointments($filters = []) {
        if (!$this->hasColumn('property_inquiry', 'appointment_status')) {
            return [];
        }

        $sql = "SELECT pi.id, pi.inquirer_name, pi.status, pi.appointment_status, pi.appointment_requested_at, pi.appointment_confirmed_at, pi.case_status,
            property.title AS property_title,
            user.uname AS agent_name
                FROM property_inquiry pi
                LEFT JOIN property ON property.pid = pi.property_id
                LEFT JOIN user ON user.uid = pi.agent_uid
                WHERE pi.appointment_status <> 'none'";

        if (!empty($filters['appointment_status'])) {
            $sql .= " AND pi.appointment_status = :appointment_status";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (pi.inquirer_name LIKE :search OR user.uname LIKE :search OR property.title LIKE :search)";
        }

        // date range filter (on confirmed or requested timestamp)
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $sql .= " AND (COALESCE(pi.appointment_confirmed_at, pi.appointment_requested_at, pi.created_at) >= :date_from_start)";
            if (!empty($filters['date_to'])) {
                $sql .= " AND (COALESCE(pi.appointment_confirmed_at, pi.appointment_requested_at, pi.created_at) <= :date_to_end)";
            }
        }

        $sql .= " ORDER BY COALESCE(pi.appointment_confirmed_at, pi.appointment_requested_at, pi.created_at) DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET :offset";
            }
        }

        $this->db->query($sql);

        if (!empty($filters['appointment_status'])) {
            $this->db->bind(':appointment_status', $filters['appointment_status']);
        }
        if (!empty($filters['search'])) {
            $this->db->bind(':search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['date_from'])) {
            $this->db->bind(':date_from_start', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $this->db->bind(':date_to_end', $filters['date_to'] . ' 23:59:59');
        }
        if (!empty($filters['limit'])) {
            $this->db->bind(':limit', (int) $filters['limit'], PDO::PARAM_INT);
            if (!empty($filters['offset'])) {
                $this->db->bind(':offset', (int) $filters['offset'], PDO::PARAM_INT);
            }
        }

        return $this->db->resultSet();
    }

    public function countAdminAppointments($filters = []) {
        if (!$this->hasColumn('property_inquiry', 'appointment_status')) {
            return 0;
        }

        $sql = "SELECT COUNT(*) AS total FROM property_inquiry pi
                LEFT JOIN property ON property.pid = pi.property_id
                LEFT JOIN user ON user.uid = pi.agent_uid
                WHERE pi.appointment_status <> 'none'";

        if (!empty($filters['appointment_status'])) {
            $sql .= " AND pi.appointment_status = :appointment_status";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (pi.inquirer_name LIKE :search OR user.uname LIKE :search OR property.title LIKE :search)";
        }

        $this->db->query($sql);
        if (!empty($filters['appointment_status'])) {
            $this->db->bind(':appointment_status', $filters['appointment_status']);
        }
        if (!empty($filters['search'])) {
            $this->db->bind(':search', '%' . $filters['search'] . '%');
        }

        $row = $this->db->single();
        return $row ? (int) $row['total'] : 0;
    }

    public function getAdminDeposits($filters = []) {
        return [];
    }

    public function countAdminDeposits($filters = []) {
        return 0;
    }
}
