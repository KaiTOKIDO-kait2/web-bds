<?php
class User {
    private $db;
    private $lastLoginError = 'invalid_credentials';

    public function __construct() {
        $this->db = new Database;
        $this->ensurePasswordColumnLength();
        $this->ensureMustResetPasswordColumn();
        $this->ensureBlockedColumn();
        $this->markInvalidPasswordHashesForReset();
    }

    public function getLastLoginError() {
        return $this->lastLoginError;
    }

    private function ensurePasswordColumnLength() {
        $this->db->query("SHOW COLUMNS FROM user LIKE 'upass'");
        $column = $this->db->single();

        if (!$column || empty($column['Type'])) {
            return;
        }

        if (preg_match('/varchar\((\d+)\)/i', $column['Type'], $matches)) {
            $length = (int) $matches[1];

            if ($length < 255) {
                $this->db->query("ALTER TABLE user MODIFY upass VARCHAR(255) NOT NULL");
                $this->db->execute();
            }
        }
    }

    private function ensureMustResetPasswordColumn() {
        $this->db->query("SHOW COLUMNS FROM user LIKE 'must_reset_password'");
        $column = $this->db->single();

        if (!$column) {
            $this->db->query("ALTER TABLE user ADD COLUMN must_reset_password TINYINT(1) NOT NULL DEFAULT 0 AFTER upass");
            $this->db->execute();
        }
    }

    private function ensureBlockedColumn() {
        $this->db->query("SHOW COLUMNS FROM user LIKE 'blocked'");
        $column = $this->db->single();

        if (!$column) {
            $this->db->query("ALTER TABLE user ADD COLUMN blocked TINYINT(1) NOT NULL DEFAULT 0 AFTER utype");
            $this->db->execute();
        }
    }

    private function markInvalidPasswordHashesForReset() {
        $this->db->query("UPDATE user SET must_reset_password = 1 WHERE upass IS NOT NULL AND LENGTH(upass) > 40 AND LENGTH(upass) < 60");
        $this->db->execute();
    }

    private function isLikelyTruncatedHash($hash) {
        $length = strlen((string) $hash);
        return $length > 40 && $length < 60;
    }

    public function login($email, $pass) {
        $this->lastLoginError = 'invalid_credentials';

        $this->db->query("SELECT * FROM user WHERE uemail = :email");
        $this->db->bind(':email', $email);
        $row = $this->db->single();

        if($row) {
            if (!empty($row['blocked'])) {
                $this->lastLoginError = 'blocked_account';
                return false;
            }

            if (!empty($row['must_reset_password'])) {
                $this->lastLoginError = 'force_reset';
                return false;
            }

            if (password_verify($pass, $row['upass'])) {
                if (password_needs_rehash($row['upass'], PASSWORD_DEFAULT)) {
                    $this->db->query("UPDATE user SET upass = :upass, must_reset_password = 0 WHERE uid = :uid");
                    $this->db->bind(':upass', password_hash($pass, PASSWORD_DEFAULT));
                    $this->db->bind(':uid', $row['uid']);
                    $this->db->execute();
                }

                return $row;
            }

            if ($row['upass'] == sha1($pass)) {
                // Nâng cấp mật khẩu legacy SHA1 lên password_hash ngay khi login thành công.
                $this->db->query("UPDATE user SET upass = :upass, must_reset_password = 0 WHERE uid = :uid");
                $this->db->bind(':upass', password_hash($pass, PASSWORD_DEFAULT));
                $this->db->bind(':uid', $row['uid']);
                $this->db->execute();
                return $row;
            }

            if ($this->isLikelyTruncatedHash($row['upass'])) {
                $this->db->query("UPDATE user SET must_reset_password = 1 WHERE uid = :uid");
                $this->db->bind(':uid', $row['uid']);
                $this->db->execute();
                $this->lastLoginError = 'force_reset';
                return false;
            }

            return false;
        } else {
            return false;
        }
    }

    public function getAgents() {
        $this->db->query("SELECT * FROM user WHERE utype='agent'");
        return $this->db->resultSet();
    }

    public function getUserById($uid) {
        $this->db->query("SELECT * FROM user WHERE uid = :uid");
        $this->db->bind(':uid', $uid);
        return $this->db->single();
    }

    public function register($data) {
        $this->db->query("INSERT INTO user (uname, uemail, uphone, upass, utype, uimage) VALUES (:uname, :uemail, :uphone, :upass, :utype, :uimage)");
        
        $this->db->bind(':uname', $data['uname']);
        $this->db->bind(':uemail', $data['uemail']);
        $this->db->bind(':uphone', $data['uphone']);
        $this->db->bind(':upass', password_hash($data['upass'], PASSWORD_DEFAULT));
        $this->db->bind(':utype', $data['utype']);
        $this->db->bind(':uimage', $data['uimage']);

        return $this->db->execute();
    }

    public function findUserByEmail($email) {
        $this->db->query("SELECT uid FROM user WHERE uemail = :email");
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        return $row !== false && !empty($row);
    }

    public function getUserByEmail($email) {
        $this->db->query("SELECT * FROM user WHERE uemail = :email LIMIT 1");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    public function updatePasswordById($uid, $newPassword) {
        $this->db->query("UPDATE user SET upass = :upass, must_reset_password = 0 WHERE uid = :uid");
        $this->db->bind(':upass', password_hash($newPassword, PASSWORD_DEFAULT));
        $this->db->bind(':uid', $uid);
        return $this->db->execute();
    }

    public function verifyPasswordById($uid, $password) {
        $user = $this->getUserById($uid);

        if (!$user || empty($user['upass'])) {
            return false;
        }

        if (password_verify($password, $user['upass'])) {
            return true;
        }

        return $user['upass'] == sha1($password);
    }

    public function countUsers() {
        $this->db->query("SELECT count(uid) as total FROM user");
        $row = $this->db->single();
        return $row ? $row['total'] : 0;
    }

    public function getUsersByType($type) {
        if ($type === 'user') {
            $this->db->query("SELECT * FROM user WHERE utype IN ('user', 'renter') ORDER BY uid DESC");
            return $this->db->resultSet();
        }

        $this->db->query("SELECT * FROM user WHERE utype = :type ORDER BY uid DESC");
        $this->db->bind(':type', $type);
        return $this->db->resultSet();
    }

    public function findUserByEmailExceptId($email, $uid) {
        $this->db->query("SELECT uid FROM user WHERE uemail = :email AND uid <> :uid LIMIT 1");
        $this->db->bind(':email', $email);
        $this->db->bind(':uid', (int) $uid);
        $row = $this->db->single();
        return $row !== false && !empty($row);
    }

    public function createUserByAdmin($data) {
        $this->db->query("INSERT INTO user (uname, uemail, uphone, upass, utype, blocked, uimage) VALUES (:uname, :uemail, :uphone, :upass, :utype, :blocked, :uimage)");
        $this->db->bind(':uname', $data['uname']);
        $this->db->bind(':uemail', $data['uemail']);
        $this->db->bind(':uphone', $data['uphone']);
        $this->db->bind(':upass', password_hash($data['upass'], PASSWORD_DEFAULT));
        $this->db->bind(':utype', $data['utype']);
        $this->db->bind(':blocked', (int) ($data['blocked'] ?? 0));
        $this->db->bind(':uimage', $data['uimage']);
        return $this->db->execute();
    }

    public function updateUserByAdmin($uid, $data) {
        $sql = "UPDATE user SET uname = :uname, uemail = :uemail, uphone = :uphone, utype = :utype, blocked = :blocked";

        if (!empty($data['upass'])) {
            $sql .= ", upass = :upass, must_reset_password = 0";
        }

        if (!empty($data['uimage'])) {
            $sql .= ", uimage = :uimage";
        }

        $sql .= " WHERE uid = :uid";

        $this->db->query($sql);
        $this->db->bind(':uname', $data['uname']);
        $this->db->bind(':uemail', $data['uemail']);
        $this->db->bind(':uphone', $data['uphone']);
        $this->db->bind(':utype', $data['utype']);
        $this->db->bind(':blocked', (int) ($data['blocked'] ?? 0));
        $this->db->bind(':uid', (int) $uid);

        if (!empty($data['upass'])) {
            $this->db->bind(':upass', password_hash($data['upass'], PASSWORD_DEFAULT));
        }

        if (!empty($data['uimage'])) {
            $this->db->bind(':uimage', $data['uimage']);
        }

        return $this->db->execute();
    }

    public function setBlockedStatus($uid, $blocked) {
        $this->db->query("UPDATE user SET blocked = :blocked WHERE uid = :uid");
        $this->db->bind(':blocked', (int) $blocked);
        $this->db->bind(':uid', (int) $uid);
        return $this->db->execute();
    }

    public function getUserProperties($uid) {
        $this->db->query("SELECT pid, title, type, stype, status, approval_status, date FROM property WHERE uid = :uid ORDER BY date DESC");
        $this->db->bind(':uid', (int) $uid);
        return $this->db->resultSet();
    }

    public function getUserActivityLogs($uid, $limit = 30) {
        $this->db->query("(
            SELECT 'property_posted' AS activity_key,
                   'Đăng bài BĐS' AS activity_label,
                   CONCAT('#', pid, ' - ', title) AS activity_detail,
                   date AS activity_date
            FROM property
            WHERE uid = :uid_property
        )
        UNION ALL
        (
            SELECT 'favorite_added' AS activity_key,
                   'Thêm vào yêu thích' AS activity_label,
                   CONCAT('Property #', pid) AS activity_detail,
                   created_at AS activity_date
            FROM property_favorite
            WHERE uid = :uid_favorite
        )
        UNION ALL
        (
            SELECT 'feedback_sent' AS activity_key,
                   'Gửi phản hồi' AS activity_label,
                   LEFT(fdescription, 120) AS activity_detail,
                   date AS activity_date
            FROM feedback
            WHERE uid = :uid_feedback
        )
        UNION ALL
        (
            SELECT 'password_reset' AS activity_key,
                   'Yêu cầu đặt lại mật khẩu' AS activity_label,
                   email AS activity_detail,
                   created_at AS activity_date
            FROM password_resets
            WHERE user_id = :uid_password_reset
        )
        ORDER BY activity_date DESC
        LIMIT :limit");

        $this->db->bind(':uid_property', (int) $uid);
        $this->db->bind(':uid_favorite', (int) $uid);
        $this->db->bind(':uid_feedback', (int) $uid);
        $this->db->bind(':uid_password_reset', (int) $uid);
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function deleteUser($uid) {
        // Find image first to unlink
        $this->db->query("SELECT uimage FROM user WHERE uid = :uid");
        $this->db->bind(':uid', $uid);
        $row = $this->db->single();

        if ($row && !empty($row['uimage'])) {
            $imgPath = "../admin/user/" . $row['uimage'];
            if(file_exists($imgPath)) {
                @unlink($imgPath);
            }
        }

        $this->db->query("DELETE FROM user WHERE uid = :uid");
        $this->db->bind(':uid', $uid);
        return $this->db->execute();
    }

    public function getUsersForDropdown() {
        $this->db->query("SELECT uid, uname FROM user ORDER BY uname ASC");
        return $this->db->resultSet();
    }

    public function updateProfile($uid, $data) {
        $sql = "UPDATE user SET uname = :uname, uphone = :uphone";

        if (!empty($data['uimage'])) {
            $sql .= ", uimage = :uimage";
        }

        $sql .= " WHERE uid = :uid";

        $this->db->query($sql);
        $this->db->bind(':uname', $data['uname']);
        $this->db->bind(':uphone', $data['uphone']);
        $this->db->bind(':uid', $uid);

        if (!empty($data['uimage'])) {
            $this->db->bind(':uimage', $data['uimage']);
        }

        return $this->db->execute();
    }
}
