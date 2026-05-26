<?php
class Location {
    private $db;
    private $hasWardsTable = false;

    public function __construct() {
        $this->db = new Database();
        $this->detectSchema();
    }

    private function detectSchema() {
        $this->db->query("SHOW TABLES LIKE 'wards'");
        $this->hasWardsTable = (bool) $this->db->single();
    }

    public function getAllCities() {
        $this->db->query("SELECT cid, code, cname FROM city ORDER BY cname");
        return $this->db->resultSet();
    }

    public function getCityById($id) {
        $this->db->query("SELECT cid, code, cname FROM city WHERE cid = :id LIMIT 1");
        $this->db->bind(':id', (int) $id);
        return $this->db->single();
    }

    public function addCity($name, $unused = null) {
        $this->db->query("INSERT INTO city (cname) VALUES (:name)");
        $this->db->bind(':name', $name);
        return $this->db->execute();
    }

    public function updateCity($id, $name, $unused = null) {
        $this->db->query("UPDATE city SET cname = :name WHERE cid = :id");
        $this->db->bind(':name', $name);
        $this->db->bind(':id', (int) $id);
        return $this->db->execute();
    }

    public function deleteCity($id) {
        $this->db->query("DELETE FROM city WHERE cid = :id");
        $this->db->bind(':id', (int) $id);
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllWards() {
        if (!$this->hasWardsTable) {
            return [];
        }

        $this->db->query("SELECT wid AS ward_id, wname AS ward_name, city_id FROM wards ORDER BY wname");
        return $this->db->resultSet();
    }

    public function getWardById($id) {
        if (!$this->hasWardsTable) {
            return null;
        }

        $this->db->query("SELECT wid AS ward_id, wname AS ward_name, city_id FROM wards WHERE wid = :id LIMIT 1");
        $this->db->bind(':id', (int) $id);
        return $this->db->single();
    }

    public function addWard($name, $cityId) {
        if (!$this->hasWardsTable) {
            return false;
        }

        $this->db->query("INSERT INTO wards (wname, city_id) VALUES (:name, :city_id)");
        $this->db->bind(':name', $name);
        $this->db->bind(':city_id', (int) $cityId);
        return $this->db->execute();
    }

    public function updateWard($id, $name, $cityId) {
        if (!$this->hasWardsTable) {
            return false;
        }

        $this->db->query("UPDATE wards SET wname = :name, city_id = :city_id WHERE wid = :id");
        $this->db->bind(':name', $name);
        $this->db->bind(':city_id', (int) $cityId);
        $this->db->bind(':id', (int) $id);
        return $this->db->execute();
    }

    public function deleteWard($id) {
        if (!$this->hasWardsTable) {
            return false;
        }

        $this->db->query("DELETE FROM wards WHERE wid = :id");
        $this->db->bind(':id', (int) $id);
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
