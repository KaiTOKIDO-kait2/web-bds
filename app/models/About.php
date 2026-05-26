<?php
class About {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAboutList() {
        $this->db->query("SELECT * FROM about ORDER BY id DESC");
        return $this->db->resultSet();
    }

    public function getAboutById($id) {
        $this->db->query("SELECT * FROM about WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addAbout($title, $content, $image) {
        $this->db->query("INSERT INTO about (title, content, image) VALUES (:title, :content, :image)");
        $this->db->bind(':title', $title);
        $this->db->bind(':content', $content);
        $this->db->bind(':image', $image);
        return $this->db->execute();
    }

    public function updateAbout($id, $title, $content, $image) {
        // Nếu không có ảnh mới, không cập nhật cột image
        if(!empty($image)) {
            $this->db->query("UPDATE about SET title = :title, content = :content, image = :image WHERE id = :id");
            $this->db->bind(':image', $image);
        } else {
            $this->db->query("UPDATE about SET title = :title, content = :content WHERE id = :id");
        }
        $this->db->bind(':title', $title);
        $this->db->bind(':content', $content);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function deleteAbout($id) {
        $this->db->query("SELECT image FROM about WHERE id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        if($row) {
            $this->db->query("DELETE FROM about WHERE id = :id");
            $this->db->bind(':id', $id);
            if($this->db->execute()) {
                if(!empty($row['image']) && file_exists("../admin/upload/" . $row['image'])) {
                    @unlink("../admin/upload/" . $row['image']);
                }
                return true;
            }
        }
        return false;
    }
}
