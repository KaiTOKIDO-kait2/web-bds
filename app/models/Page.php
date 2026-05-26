<?php
class Page {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAboutContent() {
        $this->db->query("SELECT * FROM about");
        return $this->db->resultSet();
    }

    public function saveContactMessage($data) {
        $this->db->query("INSERT INTO contact (name, email, phone, subject, message) VALUES (:name, :email, :phone, :subject, :message)");
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':subject', $data['subject']);
        $this->db->bind(':message', $data['message']);

        // Since execute returns true/false
        return $this->db->execute();
    }
}
