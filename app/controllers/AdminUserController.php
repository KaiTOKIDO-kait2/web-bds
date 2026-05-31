<?php
class AdminUserController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
    }

    // Alias để match sidebar URL: /adminUser/admin
    public function admin()
    {
        return $this->admins();
    }

    public function admins()
    {
        $adminModel = $this->model('Admin');
        $data = [
            'admins' => $adminModel->getAdmins(),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : ''
        ];
        $this->view('admin/user/admin_list', $data);
    }

    public function adminDelete($id)
    {
        $adminModel = $this->model('Admin');
        if ($adminModel->deleteAdmin($id)) {
            $msg = "<p class='alert alert-success'>Đã xóa quản trị viên</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể xóa quản trị viên</p>";
        }
        header("Location: " . BASEURL . "/adminUser/admins?msg=" . urlencode($msg));
        exit();
    }

    // Shortcut methods để match sidebar URLs:
    // /adminUser/user, /adminUser/agent
    public function user()
    {
        return $this->users('user');
    }
    public function agent()
    {
        return $this->users('agent');
    }
    public function users($type = 'user')
    {
        $this->assertAllowedType($type);

        $userModel = $this->model('User');
        $data = [
            'users' => $userModel->getUsersByType($type),
            'type' => $type,
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : ''
        ];
        $this->view('admin/user/user_list', $data);
    }

    private function assertAllowedType($type)
    {
        $allowed = ['user', 'agent'];
        if (!in_array($type, $allowed, true)) {
            header("Location: " . BASEURL . "/adminUser/users/user?msg=" . urlencode("<p class='alert alert-warning'>Loại tài khoản không hợp lệ.</p>"));
            exit();
        }
    }

    private function getUserListRedirect($type, $msg)
    {
        return BASEURL . "/adminUser/users/" . $type . "?msg=" . urlencode($msg);
    }

    private function sanitizeType($type)
    {
        return in_array($type, ['user', 'agent'], true) ? $type : 'user';
    }

    private function handleUserImageUpload($oldImage = '')
    {
        if (!isset($_FILES['uimage']) || empty($_FILES['uimage']['name'])) {
            return $oldImage;
        }

        // Use absolute path from project root
        $uploadDir = dirname(dirname(dirname(__DIR__))) . '/admin/user/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['uimage']['name']));
        $tmpName = $_FILES['uimage']['tmp_name'];

        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
            if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) {
                @unlink($uploadDir . $oldImage);
            }
            return $filename;
        }

        return $oldImage;
    }

    public function userAdd($type = 'user')
    {
        $type = $this->sanitizeType($type);
        $userModel = $this->model('User');

        $data = [
            'mode' => 'add',
            'type' => $type,
            'msg' => '',
            'error' => '',
            'user' => [
                'uname' => '',
                'uemail' => '',
                'uphone' => '',
                'utype' => $type,
                'blocked' => 0,
                'uimage' => ''
            ]
        ];

        if (isset($_POST['save'])) {
            $formData = [
                'uname' => trim($_POST['uname'] ?? ''),
                'uemail' => trim($_POST['uemail'] ?? ''),
                'uphone' => trim($_POST['uphone'] ?? ''),
                'upass' => trim($_POST['upass'] ?? ''),
                'utype' => $this->sanitizeType($_POST['utype'] ?? $type),
                'blocked' => isset($_POST['blocked']) ? 1 : 0,
                'uimage' => ''
            ];

            $data['user'] = array_merge($data['user'], $formData);

            if ($formData['uname'] === '' || $formData['uemail'] === '' || $formData['uphone'] === '' || $formData['upass'] === '') {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng nhập đầy đủ thông tin bắt buộc.</p>";
            } elseif (!filter_var($formData['uemail'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = "<p class='alert alert-warning'>Email không hợp lệ.</p>";
            } elseif ($userModel->findUserByEmail($formData['uemail'])) {
                $data['error'] = "<p class='alert alert-warning'>Email đã tồn tại.</p>";
            } else {
                $formData['uimage'] = $this->handleUserImageUpload('');
                if ($userModel->createUserByAdmin($formData)) {
                    $msg = "<p class='alert alert-success'>Đã thêm tài khoản thành công.</p>";
                    header("Location: " . $this->getUserListRedirect($formData['utype'], $msg));
                    exit();
                }
                $data['error'] = "<p class='alert alert-warning'>Không thể thêm tài khoản.</p>";
            }
        }

        $this->view('admin/user/user_form', $data);
    }

    public function userEdit($id, $type = 'user')
    {
        $type = $this->sanitizeType($type);
        $userModel = $this->model('User');
        $user = $userModel->getUserById((int) $id);

        if (!$user) {
            header("Location: " . $this->getUserListRedirect($type, "<p class='alert alert-warning'>Không tìm thấy người dùng.</p>"));
            exit();
        }

        $data = [
            'mode' => 'edit',
            'type' => $type,
            'msg' => '',
            'error' => '',
            'user' => $user
        ];

        if (isset($_POST['save'])) {
            $formData = [
                'uname' => trim($_POST['uname'] ?? ''),
                'uemail' => trim($_POST['uemail'] ?? ''),
                'uphone' => trim($_POST['uphone'] ?? ''),
                'upass' => trim($_POST['upass'] ?? ''),
                'utype' => $this->sanitizeType($_POST['utype'] ?? $type),
                'blocked' => isset($_POST['blocked']) ? 1 : 0,
                'uimage' => $user['uimage']
            ];

            $data['user'] = array_merge($user, $formData);

            if ($formData['uname'] === '' || $formData['uemail'] === '' || $formData['uphone'] === '') {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng nhập đầy đủ thông tin bắt buộc.</p>";
            } elseif (!filter_var($formData['uemail'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = "<p class='alert alert-warning'>Email không hợp lệ.</p>";
            } elseif ($userModel->findUserByEmailExceptId($formData['uemail'], (int) $id)) {
                $data['error'] = "<p class='alert alert-warning'>Email đã tồn tại.</p>";
            } else {
                $formData['uimage'] = $this->handleUserImageUpload($user['uimage']);
                if ($userModel->updateUserByAdmin((int) $id, $formData)) {
                    $msg = "<p class='alert alert-success'>Đã cập nhật tài khoản thành công.</p>";
                    header("Location: " . $this->getUserListRedirect($formData['utype'], $msg));
                    exit();
                }
                $data['error'] = "<p class='alert alert-warning'>Không thể cập nhật tài khoản.</p>";
            }
        }

        $this->view('admin/user/user_form', $data);
    }

    public function userDetail($id, $type = 'user')
    {
        $type = $this->sanitizeType($type);
        $userModel = $this->model('User');
        $user = $userModel->getUserById((int) $id);

        if (!$user) {
            header("Location: " . $this->getUserListRedirect($type, "<p class='alert alert-warning'>Không tìm thấy người dùng.</p>"));
            exit();
        }

        $data = [
            'type' => $type,
            'user' => $user,
            'properties' => $userModel->getUserProperties((int) $id),
            'activities' => $userModel->getUserActivityLogs((int) $id, 50),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : ''
        ];

        $this->view('admin/user/user_detail', $data);
    }

    public function userBlock($id, $type = 'user')
    {
        $type = $this->sanitizeType($type);
        $userModel = $this->model('User');
        $user = $userModel->getUserById((int) $id);

        if (!$user) {
            header("Location: " . $this->getUserListRedirect($type, "<p class='alert alert-warning'>Không tìm thấy người dùng.</p>"));
            exit();
        }

        $ok = $userModel->setBlockedStatus((int) $id, 1);
        $msg = $ok
            ? "<p class='alert alert-success'>Đã khóa tài khoản thành công.</p>"
            : "<p class='alert alert-warning'>Không thể khóa tài khoản.</p>";

        header("Location: " . $this->getUserListRedirect($type, $msg));
        exit();
    }

    public function userUnblock($id, $type = 'user')
    {
        $type = $this->sanitizeType($type);
        $userModel = $this->model('User');
        $user = $userModel->getUserById((int) $id);

        if (!$user) {
            header("Location: " . $this->getUserListRedirect($type, "<p class='alert alert-warning'>Không tìm thấy người dùng.</p>"));
            exit();
        }

        $ok = $userModel->setBlockedStatus((int) $id, 0);
        $msg = $ok
            ? "<p class='alert alert-success'>Đã mở khóa tài khoản thành công.</p>"
            : "<p class='alert alert-warning'>Không thể mở khóa tài khoản.</p>";

        header("Location: " . $this->getUserListRedirect($type, $msg));
        exit();
    }

    public function userDelete($id, $type = 'user')
    {
        $type = $this->sanitizeType($type);
        $userModel = $this->model('User');
        if ($userModel->deleteUser($id)) {
            $msg = "<p class='alert alert-success'>Đã xóa tài khoản thành công</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể xóa tài khoản</p>";
        }
        header("Location: " . BASEURL . "/adminUser/users/" . $type . "?msg=" . urlencode($msg));
        exit();
    }
}
