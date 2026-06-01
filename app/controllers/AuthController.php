<?php
class AuthController extends Controller {
    private function isLocalEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $check = strtolower($host . ' ' . $serverName);

        return strpos($check, 'localhost') !== false
            || strpos($check, '127.0.0.1') !== false
            || strpos($check, '::1') !== false;
    }

    private function getBaseUrlAbsolute() {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . BASEURL;
    }

    public function login() {
        $data = [
            'error' => '',
            'msg' => ''
        ];

        if (isset($_GET['msg']) && $_GET['msg'] !== '') {
            $data['msg'] = "<p class='alert alert-success'>" . htmlspecialchars($_GET['msg']) . "</p>";
        }
        
        if (isset($_POST['login'])) {
            $userModel = $this->model('User');
            $email = $_POST['email'];
            $pass = $_POST['pass'];
            
            if ($loggedInUser = $userModel->login($email, $pass)) {
                $_SESSION['uid'] = $loggedInUser['uid'];
                $_SESSION['uemail'] = $loggedInUser['uemail'];
                $_SESSION['uname'] = $loggedInUser['uname'];
                $_SESSION['utype'] = $loggedInUser['utype'];
                header('Location: ' . BASEURL . '/home/index');
                exit();
            } else {
                if ($userModel->getLastLoginError() === 'force_reset') {
                    $data['error'] = "<p class='alert alert-warning'>Tài khoản của bạn cần đặt lại mật khẩu để tiếp tục. <a href='" . BASEURL . "/auth/forgot'>Đặt lại ngay</a>.</p>";
                } elseif ($userModel->getLastLoginError() === 'blocked_account') {
                    $data['error'] = "<p class='alert alert-warning'>Tài khoản của bạn đang bị khóa. Vui lòng liên hệ quản trị viên.</p>";
                } else {
                    $data['error'] = "<p class='alert alert-warning'>Email hoặc mật khẩu không đúng!</p>";
                }
            }
        }
        
        $this->view('auth/login', $data);
    }

    public function forgot() {
        $data = [
            'error' => '',
            'msg' => '',
            'dev_link' => '',
            'is_local' => $this->isLocalEnvironment()
        ];

        if (isset($_POST['request_reset'])) {
            $email = trim($_POST['email']);

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng nhập email hợp lệ</p>";
            } else {
                $userModel = $this->model('User');
                $passwordResetModel = $this->model('PasswordReset');
                require_once dirname(__DIR__) . '/libs/Mailer.php';
                $mailer = new Mailer();
                $user = $userModel->getUserByEmail($email);

                if ($user) {
                    $rawToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $rawToken);
                    $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60));
                    $requestIp = $_SERVER['REMOTE_ADDR'] ?? null;
                    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null;

                    $passwordResetModel->createToken($user['uid'], $user['uemail'], $tokenHash, $expiresAt, $requestIp, $userAgent);

                    $resetLink = $this->getBaseUrlAbsolute() . '/auth/resetPassword?email=' . urlencode($user['uemail']) . '&token=' . urlencode($rawToken);
                    $subject = 'Yeu cau dat lai mat khau';
                    $message = "Ban da yeu cau dat lai mat khau.\n\nNhan vao link sau de dat lai mat khau (hieu luc 30 phut):\n" . $resetLink . "\n\nNeu ban khong yeu cau, vui long bo qua email nay.";
                    $mailSent = $mailer->sendResetLink($user['uemail'], $subject, $message);

                    if ($this->isLocalEnvironment() || !$mailSent) {
                        $data['dev_link'] = $resetLink;
                    }

                    if ($mailSent) {
                        $data['msg'] = "<p class='alert alert-success'>Link đặt lại mật khẩu đã được gửi, vui lòng kiểm tra Email của bạn.</p>";
                    } else {
                        $data['error'] = "<p class='alert alert-warning'>Không thể gửi email. Vui lòng kiểm tra cấu hình SMTP.</p>";
                    }
                } else {
                    $data['msg'] = "<p class='alert alert-success'>Nếu email tồn tại trong hệ thống, link đặt lại mật khẩu đã được gửi.</p>";
                }
            }
        }

        $this->view('auth/forgot', $data);
    }

    public function resetPassword() {
        $data = [
            'error' => '',
            'msg' => '',
            'email' => trim($_GET['email'] ?? ''),
            'token' => trim($_GET['token'] ?? ''),
            'is_valid' => false
        ];

        $passwordResetModel = $this->model('PasswordReset');
        $userModel = $this->model('User');

        if (empty($data['email']) || empty($data['token'])) {
            $data['error'] = "<p class='alert alert-warning'>Liên kết đặt lại mật khẩu không hợp lệ.</p>";
            $this->view('auth/reset_password', $data);
            return;
        }

        $resetRecord = $passwordResetModel->findValidToken($data['email'], $data['token']);
        if (!$resetRecord) {
            $data['error'] = "<p class='alert alert-warning'>Liên kết đã hết hạn hoặc không tồn tại.</p>";
            $this->view('auth/reset_password', $data);
            return;
        }

        $data['is_valid'] = true;

        if (isset($_POST['update_password'])) {
            $email = trim($_POST['email'] ?? '');
            $token = trim($_POST['token'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            $data['email'] = $email;
            $data['token'] = $token;

            if (empty($password) || empty($confirmPassword)) {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng nhập đầy đủ mật khẩu mới.</p>";
            } elseif (strlen($password) < 6) {
                $data['error'] = "<p class='alert alert-warning'>Mật khẩu phải có ít nhất 6 ký tự.</p>";
            } elseif ($password !== $confirmPassword) {
                $data['error'] = "<p class='alert alert-warning'>Mật khẩu xác nhận không khớp.</p>";
            } else {
                $validRecord = $passwordResetModel->findValidToken($email, $token);
                if (!$validRecord) {
                    $data['is_valid'] = false;
                    $data['error'] = "<p class='alert alert-warning'>Phiên đặt lại mật khẩu không còn hợp lệ.</p>";
                } else {
                    if ($userModel->updatePasswordById($validRecord['user_id'], $password)) {
                        $passwordResetModel->markUsed($validRecord['id']);
                        header('Location: ' . BASEURL . '/auth/login?msg=' . urlencode('Đặt lại mật khẩu thành công. Vui lòng đăng nhập.'));
                        exit();
                    } else {
                        $data['error'] = "<p class='alert alert-warning'>Không thể cập nhật mật khẩu. Vui lòng thử lại.</p>";
                    }
                }
            }
        }

        $this->view('auth/reset_password', $data);
    }

    public function register() {
        $data = [
            'msg' => '',
            'error' => ''
        ];
        $allowedAccountTypes = ['renter', 'owner', 'agent'];

        if(isset($_POST['reg'])) {
            $userModel = $this->model('User');
            
            $postData = [
                'uname' => trim($_POST['name']),
                'uemail' => trim($_POST['email']),
                'uphone' => trim($_POST['phone']),
                'upass' => trim($_POST['pass']),
                'utype' => trim($_POST['utype'] ?? 'renter')
            ];

            if (!in_array($postData['utype'], $allowedAccountTypes, true)) {
                $postData['utype'] = 'renter';
            }

            // Handle optional file upload
            $uimage = '';
            if(isset($_FILES['uimage']['name']) && !empty($_FILES['uimage']['name'])) {
                $uimage = time() . '_' . basename($_FILES['uimage']['name']);
                $temp_name = $_FILES['uimage']['tmp_name'];
                // Use absolute path from project root
                $uploadDir = dirname(dirname(__DIR__)) . '/admin/user/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                move_uploaded_file($temp_name, $uploadDir . $uimage);
            }
            $postData['uimage'] = $uimage;

            if(!empty($postData['uname']) && !empty($postData['uemail']) && !empty($postData['uphone']) && !empty($postData['upass'])) {
                if($userModel->findUserByEmail($postData['uemail'])) {
                    $data['error'] = "<p class='alert alert-warning'>Email đã tồn tại</p>";
                } else {
                    if($userModel->register($postData)) {
                        header('Location: ' . BASEURL . '/home/index?msg=' . urlencode('Đăng ký thành công. Chào mừng bạn đến với hệ thống.'));
                        exit;
                    } else {
                        $data['error'] = "<p class='alert alert-warning'>Đăng ký không thành công</p>";
                    }
                }
            } else {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng điền đầy đủ thông tin</p>";
            }
        }

        $this->view('auth/register', $data);
    }
    
    public function logout() {
        session_destroy();
        header('Location: ' . BASEURL . '/auth/login');
        exit();
    }
}
