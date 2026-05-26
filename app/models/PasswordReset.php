<?php
class PasswordReset {
    private $db;

    public function __construct() {
        $this->db = new Database;
        $this->ensureTable();
    }

    private function ensureTable() {
        $sql = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email VARCHAR(100) NOT NULL,
            token_hash VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            request_ip VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_expires_at (expires_at),
            INDEX idx_token_hash (token_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->query($sql);
        $this->db->execute();
    }

    public function createToken($userId, $email, $tokenHash, $expiresAt, $requestIp = null, $userAgent = null) {
        $this->db->query("UPDATE password_resets SET used_at = NOW() WHERE email = :email AND used_at IS NULL");
        $this->db->bind(':email', $email);
        $this->db->execute();

        $this->db->query("INSERT INTO password_resets (user_id, email, token_hash, expires_at, request_ip, user_agent) VALUES (:user_id, :email, :token_hash, :expires_at, :request_ip, :user_agent)");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':email', $email);
        $this->db->bind(':token_hash', $tokenHash);
        $this->db->bind(':expires_at', $expiresAt);
        $this->db->bind(':request_ip', $requestIp);
        $this->db->bind(':user_agent', $userAgent);

        return $this->db->execute();
    }

    public function findValidToken($email, $rawToken) {
        $tokenHash = hash('sha256', $rawToken);
        $currentTime = date('Y-m-d H:i:s');

        $this->db->query("SELECT * FROM password_resets WHERE email = :email AND token_hash = :token_hash AND used_at IS NULL AND expires_at > :current_time ORDER BY id DESC LIMIT 1");
        $this->db->bind(':email', $email);
        $this->db->bind(':token_hash', $tokenHash);
        $this->db->bind(':current_time', $currentTime);

        return $this->db->single();
    }

    public function markUsed($id) {
        $this->db->query("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
