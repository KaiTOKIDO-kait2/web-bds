<?php
class PropertyType {
    private $db;

    public function __construct() {
        $this->db = new Database;
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS property_type (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(120) NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_property_type_name (name),
                UNIQUE KEY uq_property_type_slug (slug),
                INDEX idx_property_type_active_sort (is_active, sort_order, id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->execute();
        } catch (Throwable $e) {
            // Keep app running even if schema changes are restricted.
        }
    }

    private function slugify($text) {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii !== false) {
            $text = $ascii;
        }

        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');

        return $text !== '' ? $text : ('type-' . substr(md5((string) microtime(true)), 0, 8));
    }

    private function slugExists($slug, $excludeId = 0) {
        $sql = "SELECT id FROM property_type WHERE slug = :slug";
        if ($excludeId > 0) {
            $sql .= " AND id <> :exclude_id";
        }
        $sql .= " LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':slug', $slug);
        if ($excludeId > 0) {
            $this->db->bind(':exclude_id', $excludeId);
        }

        return !empty($this->db->single());
    }

    private function buildUniqueSlug($baseSlug, $excludeId = 0) {
        $slug = $baseSlug !== '' ? $baseSlug : ('type-' . substr(md5((string) microtime(true)), 0, 8));
        $candidate = $slug;
        $i = 2;

        while ($this->slugExists($candidate, $excludeId)) {
            $candidate = $slug . '-' . $i;
            $i++;
        }

        return $candidate;
    }

    public function getAll($includeInactive = true) {
        $sql = "SELECT * FROM property_type";
        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, name ASC";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM property_type WHERE id = :id LIMIT 1");
        $this->db->bind(':id', (int) $id);
        return $this->db->single();
    }

    public function create($name, $slug = '', $sortOrder = 0, $isActive = 1) {
        $name = trim((string) $name);
        if ($name === '') {
            return ['ok' => false, 'message' => 'Tên loại không được để trống.'];
        }

        $this->db->query("SELECT id FROM property_type WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) LIMIT 1");
        $this->db->bind(':name', $name);
        if (!empty($this->db->single())) {
            return ['ok' => false, 'message' => 'Loại bất động sản đã tồn tại.'];
        }

        $slug = trim((string) $slug);
        $slug = $slug !== '' ? $this->slugify($slug) : $this->slugify($name);
        $slug = $this->buildUniqueSlug($slug);

        $this->db->query("INSERT INTO property_type (name, slug, is_active, sort_order) VALUES (:name, :slug, :is_active, :sort_order)");
        $this->db->bind(':name', $name);
        $this->db->bind(':slug', $slug);
        $this->db->bind(':is_active', (int) $isActive);
        $this->db->bind(':sort_order', (int) $sortOrder);

        if ($this->db->execute()) {
            return ['ok' => true, 'message' => 'Thêm loại bất động sản thành công.'];
        }

        return ['ok' => false, 'message' => 'Không thể thêm loại bất động sản.'];
    }

    public function update($id, $name, $slug = '', $sortOrder = 0, $isActive = 1) {
        $id = (int) $id;
        if ($id <= 0) {
            return ['ok' => false, 'message' => 'ID loại bất động sản không hợp lệ.'];
        }

        $name = trim((string) $name);
        if ($name === '') {
            return ['ok' => false, 'message' => 'Tên loại không được để trống.'];
        }

        $this->db->query("SELECT id FROM property_type WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) AND id <> :id LIMIT 1");
        $this->db->bind(':name', $name);
        $this->db->bind(':id', $id);
        if (!empty($this->db->single())) {
            return ['ok' => false, 'message' => 'Tên loại đã tồn tại.'];
        }

        $slug = trim((string) $slug);
        $slug = $slug !== '' ? $this->slugify($slug) : $this->slugify($name);
        $slug = $this->buildUniqueSlug($slug, $id);

        $this->db->query("UPDATE property_type
            SET name = :name, slug = :slug, sort_order = :sort_order, is_active = :is_active
            WHERE id = :id");
        $this->db->bind(':name', $name);
        $this->db->bind(':slug', $slug);
        $this->db->bind(':sort_order', (int) $sortOrder);
        $this->db->bind(':is_active', (int) $isActive);
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            return ['ok' => true, 'message' => 'Cập nhật loại bất động sản thành công.'];
        }

        return ['ok' => false, 'message' => 'Không thể cập nhật loại bất động sản.'];
    }

    public function toggleActive($id) {
        $row = $this->getById($id);
        if (empty($row)) {
            return ['ok' => false, 'message' => 'Không tìm thấy loại bất động sản.'];
        }

        $next = ((int) $row['is_active'] === 1) ? 0 : 1;
        $this->db->query("UPDATE property_type SET is_active = :is_active WHERE id = :id");
        $this->db->bind(':is_active', $next);
        $this->db->bind(':id', (int) $id);

        if ($this->db->execute()) {
            return ['ok' => true, 'message' => $next === 1 ? 'Đã bật lại loại bất động sản.' : 'Đã ẩn loại bất động sản.'];
        }

        return ['ok' => false, 'message' => 'Không thể thay đổi trạng thái loại bất động sản.'];
    }

    public function delete($id) {
        $row = $this->getById($id);
        if (empty($row)) {
            return ['ok' => false, 'message' => 'Không tìm thấy loại bất động sản.'];
        }

        $name = (string) ($row['name'] ?? '');

        $this->db->query("SELECT COUNT(*) AS total FROM property WHERE type_id = :id OR LOWER(TRIM(type)) = LOWER(TRIM(:name))");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':name', $name);
        $used = $this->db->single();
        $totalUsed = !empty($used['total']) ? (int) $used['total'] : 0;

        if ($totalUsed > 0) {
            return ['ok' => false, 'message' => 'Loại đang được dùng bởi ' . $totalUsed . ' bất động sản. Hãy ẩn thay vì xóa.'];
        }

        $this->db->query("DELETE FROM property_type WHERE id = :id");
        $this->db->bind(':id', (int) $id);
        if ($this->db->execute()) {
            return ['ok' => true, 'message' => 'Đã xóa loại bất động sản.'];
        }

        return ['ok' => false, 'message' => 'Không thể xóa loại bất động sản.'];
    }
}
