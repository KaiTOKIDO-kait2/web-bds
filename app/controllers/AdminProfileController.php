<?php
class AdminProfileController extends Controller {
    private function requireAdmin() {
        if (!isset($_SESSION['auser'])) {
            header('Location: ' . BASEURL . '/admin/index');
            exit;
        }

        $adminModel = $this->model('Admin');
        $admin = null;

        if (!empty($_SESSION['aid'])) {
            $admin = $adminModel->getAdminById((int) $_SESSION['aid']);
        }

        if (empty($admin) && !empty($_SESSION['auser'])) {
            $admin = $adminModel->getAdminByUser((string) $_SESSION['auser']);
        }

        if (empty($admin)) {
            unset($_SESSION['aid'], $_SESSION['auser'], $_SESSION['aemail'], $_SESSION['adob'], $_SESSION['aphone']);
            header('Location: ' . BASEURL . '/admin/index');
            exit;
        }

        $_SESSION['aid'] = (int) $admin['aid'];
        $_SESSION['auser'] = (string) $admin['auser'];
        $_SESSION['aemail'] = (string) $admin['aemail'];
        $_SESSION['adob'] = (string) $admin['adob'];
        $_SESSION['aphone'] = (string) $admin['aphone'];

        return $admin;
    }

    public function index() {
        $adminModel = $this->model('Admin');
        $admin = $this->requireAdmin();

        $data = [
            'admin' => $admin,
            'popup' => null,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_admin_profile'])) {
                $userName = trim((string) ($_POST['auser'] ?? ''));
                $email = trim((string) ($_POST['aemail'] ?? ''));
                $dob = trim((string) ($_POST['adob'] ?? ''));
                $phone = trim((string) ($_POST['aphone'] ?? ''));

                if ($userName === '' || $email === '' || $dob === '' || $phone === '') {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin hồ sơ admin'];
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Email admin không hợp lệ'];
                } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Ngày sinh admin không hợp lệ'];
                } elseif (!preg_match('/^[0-9]{9,15}$/', $phone)) {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Số điện thoại admin không hợp lệ'];
                } else {
                    if ($adminModel->updateAdminProfile((int) $admin['aid'], [
                        'auser' => $userName,
                        'aemail' => $email,
                        'adob' => $dob,
                        'aphone' => $phone,
                    ])) {
                        $_SESSION['auser'] = $userName;
                        $_SESSION['aemail'] = $email;
                        $_SESSION['adob'] = $dob;
                        $_SESSION['aphone'] = $phone;
                        $data['admin'] = $adminModel->getAdminById((int) $admin['aid']);
                        $data['popup'] = ['type' => 'success', 'message' => 'Cập nhật hồ sơ admin thành công'];
                    } else {
                        $data['popup'] = ['type' => 'warning', 'message' => 'Cập nhật hồ sơ admin không thành công'];
                    }
                }
            }

            if (isset($_POST['change_admin_password'])) {
                $currentPassword = trim((string) ($_POST['current_password'] ?? ''));
                $newPassword = trim((string) ($_POST['new_password'] ?? ''));
                $confirmPassword = trim((string) ($_POST['confirm_password'] ?? ''));

                if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin mật khẩu'];
                } elseif (strlen($newPassword) < 6) {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'];
                } elseif ($newPassword !== $confirmPassword) {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu nhập lại không khớp'];
                } elseif ($currentPassword === $newPassword) {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu mới phải khác mật khẩu hiện tại'];
                } else {
                    if ($adminModel->updateAdminPassword((int) $admin['aid'], $currentPassword, $newPassword)) {
                        $data['popup'] = ['type' => 'success', 'message' => 'Đổi mật khẩu admin thành công'];
                    } else {
                        $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu hiện tại không đúng hoặc không thể đổi mật khẩu'];
                    }
                }
            }

            $admin = $this->requireAdmin();
            $data['admin'] = $admin;
        }

        $this->view('admin/profile/index', $data);
    }
}