<?php
class Admin {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function login($user, $pass) {
        $this->db->query("SELECT * FROM admin WHERE auser = :user AND apass = :pass");
        $this->db->bind(':user', $user);
        $this->db->bind(':pass', sha1($pass)); 
        
        return $this->db->single();
    }

    public function getAdmins() {
        $this->db->query("SELECT * FROM admin");
        return $this->db->resultSet();
    }

    public function deleteAdmin($id) {
        $this->db->query("DELETE FROM admin WHERE aid = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    private function hasTable($tableName) {
        $this->db->query("SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name");
        $this->db->bind(':table_name', $tableName);
        $row = $this->db->single();
        return !empty($row) && (int) $row['total'] > 0;
    }

    private function hasColumn($tableName, $columnName) {
        $this->db->query("SELECT COUNT(*) AS total FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table_name AND column_name = :column_name");
        $this->db->bind(':table_name', $tableName);
        $this->db->bind(':column_name', $columnName);
        $row = $this->db->single();
        return !empty($row) && (int) $row['total'] > 0;
    }

    private function scalar($sql, $bindings = []) {
        $this->db->query($sql);
        foreach ($bindings as $param => $value) {
            $this->db->bind($param, $value);
        }
        $row = $this->db->single();
        if (empty($row)) {
            return 0;
        }

        $value = reset($row);
        if ($value === null || $value === false) {
            return 0;
        }

        return is_numeric($value) ? (float) $value : 0;
    }

    private function rows($sql, $bindings = []) {
        $this->db->query($sql);
        foreach ($bindings as $param => $value) {
            $this->db->bind($param, $value);
        }
        return $this->db->resultSet();
    }

    public function getDashboardStats() {
        $stats = [];

        $stats['users'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM user WHERE utype = 'user'");
        $stats['owners'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM user WHERE utype = 'owner'");
        $stats['agents'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM user WHERE utype = 'agent'");
        $stats['properties'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM property");

        $stats['property_type_cards'] = [
            ['id' => 1, 'label' => 'Căn hộ chung cư', 'count' => 0, 'icon' => 'fa-building'],
            ['id' => 2, 'label' => 'Chung cư mini', 'count' => 0, 'icon' => 'fa-th-large'],
            ['id' => 3, 'label' => 'Nhà', 'count' => 0, 'icon' => 'fa-home'],
            ['id' => 4, 'label' => 'Biệt thự', 'count' => 0, 'icon' => 'fa-institution'],
            ['id' => 5, 'label' => 'Nhà mặt phố', 'count' => 0, 'icon' => 'fa-road'],
            ['id' => 6, 'label' => 'Nhà trọ', 'count' => 0, 'icon' => 'fa-bed'],
            ['id' => 7, 'label' => 'Văn phòng', 'count' => 0, 'icon' => 'fa-briefcase'],
        ];
        if ($this->hasTable('property_type') && $this->hasColumn('property', 'type_id')) {
            $counts = $this->rows("SELECT type_id, COUNT(*) AS total FROM property WHERE type_id IS NOT NULL GROUP BY type_id");
            $byId = [];
            foreach ($counts as $row) {
                $byId[(int) $row['type_id']] = (int) $row['total'];
            }
            foreach ($stats['property_type_cards'] as $idx => $row) {
                $id = (int) $row['id'];
                $stats['property_type_cards'][$idx]['count'] = $byId[$id] ?? 0;
            }
        } elseif ($this->hasColumn('property', 'type')) {
            foreach ($stats['property_type_cards'] as $idx => $row) {
                $label = (string) $row['label'];
                $stats['property_type_cards'][$idx]['count'] = (int) $this->scalar(
                    "SELECT COUNT(*) AS total FROM property WHERE `type` = :t",
                    [':t' => $label]
                );
            }
        }

        $stats['sales'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM property WHERE stype = 'sale'");
        $stats['rents'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM property WHERE stype = 'rent'");

        $stats['total_transactions'] = 0;
        $stats['accepted_transactions'] = 0;
        $stats['close_rate'] = 0;
        $stats['chart_transactions_by_day'] = [];
        $stats['chart_properties_by_area'] = [];
        $stats['chart_avg_price_by_type'] = [];

        if ($this->hasTable('property_inquiry')) {
            $stats['total_transactions'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM property_inquiry");

            if ($this->hasColumn('property_inquiry', 'status')) {
                $stats['accepted_transactions'] = (int) $this->scalar("SELECT COUNT(*) AS total FROM property_inquiry WHERE status = 'accepted'");
            }

            if ($stats['total_transactions'] > 0) {
                $stats['close_rate'] = round(($stats['accepted_transactions'] / $stats['total_transactions']) * 100, 2);
            }

            $stats['chart_transactions_by_day'] = $this->rows(
                "SELECT DATE(created_at) AS label, COUNT(*) AS total
                 FROM property_inquiry
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY DATE(created_at) ASC"
            );
        }

        $areaExpr = null;
        if ($this->hasColumn('property', 'city_id')) {
            $areaExpr = 'CAST(city_id AS CHAR)';
        } elseif ($this->hasColumn('property', 'city')) {
            $areaExpr = 'city';
        } elseif ($this->hasColumn('property', 'location')) {
            $areaExpr = 'location';
        }

        if ($areaExpr !== null) {
            $stats['chart_properties_by_area'] = $this->rows(
                "SELECT {$areaExpr} AS label, COUNT(*) AS total
                 FROM property
                 GROUP BY {$areaExpr}
                 ORDER BY total DESC
                 LIMIT 8"
            );
        }

        if ($this->hasColumn('property', 'type') && $this->hasColumn('property', 'price')) {
            $stats['chart_avg_price_by_type'] = $this->rows(
                "SELECT type AS label, AVG(price) AS avg_price
                 FROM property
                 WHERE price IS NOT NULL AND price > 0
                 GROUP BY type
                 ORDER BY avg_price DESC
                 LIMIT 8"
            );
        }

        return $stats;
    }
}
