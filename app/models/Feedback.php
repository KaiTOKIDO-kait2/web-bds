<?php
class Feedback {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getApprovedFeedback() {
        $this->db->query("SELECT feedback.*, user.* FROM feedback, user WHERE feedback.uid=user.uid AND feedback.status='1'");
        return $this->db->resultSet();
    }

    public function getAllFeedback() {
        $this->db->query("SELECT feedback.*, user.uname, user.uemail FROM feedback LEFT JOIN user ON feedback.uid = user.uid ORDER BY feedback.date DESC, feedback.fid DESC");
        return $this->db->resultSet();
    }

    public function getFeedbackById($id) {
        $this->db->query("SELECT * FROM feedback WHERE fid = :id");
        $this->db->bind(':id', (int) $id);
        return $this->db->single();
    }

    public function updateFeedbackStatus($id, $status) {
        $feedback = $this->getFeedbackById($id);
        if (!$feedback) {
            return false;
        }

        $this->db->query("UPDATE feedback SET status = :status WHERE fid = :id");
        $this->db->bind(':status', (int) $status);
        $this->db->bind(':id', (int) $id);

        if (!$this->db->execute()) {
            return false;
        }

        return true;
    }

    public function deleteFeedback($id) {
        $this->db->query("DELETE FROM feedback WHERE fid = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function addFeedback($uid, $content) {
        $this->db->query("INSERT INTO feedback (uid,fdescription,status) VALUES (:uid,:content,'0')");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':content', $content);
        return $this->db->execute();
    }
}
