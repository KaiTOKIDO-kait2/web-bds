<?php
class AdminController extends Controller
{
    public function index()
    {
        if (isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/dashboard");
            exit();
        }

        $data = ['error' => ''];

        if (isset($_POST['login'])) {
            $user = $_POST['user'];
            $pass = $_POST['pass'];

            if (!empty($user) && !empty($pass)) {
                $adminModel = $this->model('Admin');
                $loggedInAdmin = $adminModel->login($user, $pass);

                if ($loggedInAdmin) {
                    $_SESSION['aid'] = (int) $loggedInAdmin['aid'];
                    $_SESSION['auser'] = $loggedInAdmin['auser'];
                    $_SESSION['aemail'] = $loggedInAdmin['aemail'];
                    $_SESSION['adob'] = $loggedInAdmin['adob'];
                    $_SESSION['aphone'] = $loggedInAdmin['aphone'];
                    header("Location: " . BASEURL . "/admin/dashboard");
                    exit();
                } else {
                    $data['error'] = '* Tên đăng nhập hoặc mật khẩu không đúng';
                }
            } else {
                $data['error'] = '* Vui lòng nhập đầy đủ thông tin!';
            }
        }

        $this->view('admin/index', $data);
    }

    public function dashboard()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }

        $adminModel = $this->model('Admin');
        $data = [
            'stats' => $adminModel->getDashboardStats()
        ];

        $this->view('admin/dashboard', $data);
    }

    public function logout()
    {
        unset($_SESSION['aid']);
        unset($_SESSION['auser']);
        unset($_SESSION['aemail']);
        unset($_SESSION['adob']);
        unset($_SESSION['aphone']);
        header("Location: " . BASEURL . "/admin/index");
        exit();
    }
}
