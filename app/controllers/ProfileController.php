<?php
class ProfileController extends Controller {
    public function index() {
        if(!isset($_SESSION['uemail'])) {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        $userModel = $this->model('User');
        $feedbackModel = $this->model('Feedback');

        $data = [
            'user' => $userModel->getUserById($_SESSION['uid']),
            'msg' => '',
            'error' => '',
            'profile_msg' => '',
            'profile_error' => '',
            'password_msg' => '',
            'password_error' => '',
            'popup' => null
        ];

        if(isset($_POST['update_profile'])) {
            $uid = $_SESSION['uid'];
            $name = trim($_POST['uname']);
            $phone = trim($_POST['uphone']);

            if(empty($name) || empty($phone)) {
                $data['popup'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ họ tên và số điện thoại'];
            } elseif(!preg_match('/^[0-9]{9,11}$/', $phone)) {
                $data['popup'] = ['type' => 'warning', 'message' => 'Số điện thoại không hợp lệ (chỉ gồm 9-11 chữ số)'];
            } else {
                $updateData = [
                    'uname' => $name,
                    'uphone' => $phone,
                    'uimage' => ''
                ];

                $currentUser = $userModel->getUserById($uid);

                if(isset($_FILES['uimage']) && $_FILES['uimage']['error'] === UPLOAD_ERR_OK) {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    $originalName = $_FILES['uimage']['name'];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    if(!in_array($ext, $allowedExtensions, true)) {
                        $data['popup'] = ['type' => 'warning', 'message' => 'Ảnh đại diện chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP'];
                    } else {
                        $newImageName = time() . '_user_' . mt_rand(1000, 9999) . '.' . $ext;
                        $uploadPath = "../admin/user/" . $newImageName;

                        if(move_uploaded_file($_FILES['uimage']['tmp_name'], $uploadPath)) {
                            $updateData['uimage'] = $newImageName;

                            if(!empty($currentUser['uimage'])) {
                                $oldImagePath = "../admin/user/" . $currentUser['uimage'];
                                if(file_exists($oldImagePath)) {
                                    @unlink($oldImagePath);
                                }
                            }
                        } else {
                            $data['popup'] = ['type' => 'warning', 'message' => 'Không thể tải lên ảnh đại diện'];
                        }
                    }
                }

                if(empty($data['popup'])) {
                    if($userModel->updateProfile($uid, $updateData)) {
                        $_SESSION['uname'] = $name;
                        $data['popup'] = ['type' => 'success', 'message' => 'Cập nhật thông tin cá nhân thành công'];
                        $data['user'] = $userModel->getUserById($uid);
                    } else {
                        $data['popup'] = ['type' => 'warning', 'message' => 'Cập nhật thông tin không thành công'];
                    }
                }
            }
        }

        if(isset($_POST['change_password'])) {
            $uid = $_SESSION['uid'];
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if(empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $data['popup'] = ['type' => 'warning', 'message' => 'Vui lòng nhập đầy đủ thông tin mật khẩu'];
            } elseif(!$userModel->verifyPasswordById($uid, $currentPassword)) {
                $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu hiện tại không đúng'];
            } elseif(strlen($newPassword) < 6) {
                $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'];
            } elseif($newPassword !== $confirmPassword) {
                $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu nhập lại không khớp'];
            } elseif($currentPassword === $newPassword) {
                $data['popup'] = ['type' => 'warning', 'message' => 'Mật khẩu mới phải khác mật khẩu hiện tại'];
            } else {
                if($userModel->updatePasswordById($uid, $newPassword)) {
                    $data['popup'] = ['type' => 'success', 'message' => 'Đổi mật khẩu thành công'];
                } else {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Đổi mật khẩu không thành công'];
                }
            }
        }

        if(isset($_POST['insert_feedback'])) {
            $uid = $_SESSION['uid'];
            $content = trim($_POST['content']);

            if(!empty($content)) {
                if($feedbackModel->addFeedback($uid, $content)) {
                    $data['popup'] = ['type' => 'success', 'message' => 'Gửi phản hồi thành công'];
                } else {
                    $data['popup'] = ['type' => 'warning', 'message' => 'Gửi phản hồi không thành công'];
                }
            } else {
                $data['popup'] = ['type' => 'warning', 'message' => 'Vui lòng nhập nội dung phản hồi'];
            }
        }

        $this->view('profile/index', $data);
    }
}
