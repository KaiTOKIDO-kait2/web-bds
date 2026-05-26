<?php
class Contact {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Frontend: Gửi liên hệ mới
    public function addContact($name, $email, $phone, $subject, $message) {
        $this->db->query("INSERT INTO contact (name, email, phone, subject, message) VALUES (:name, :email, :phone, :subject, :message)");
        $this->db->bind(':name', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':subject', $subject);
        $this->db->bind(':message', $message);
        return $this->db->execute();
    }

    // Admin: Lấy danh sách liên hệ
    public function getAllContacts() {
        $this->db->query("SELECT * FROM contact ORDER BY cid DESC");
        return $this->db->resultSet();
    }

    // Admin: Phản hồi liên hệ (có thể thêm sau, hiện tại để xóa)
    public function deleteContact($id) {
        $this->db->query("DELETE FROM contact WHERE cid = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
