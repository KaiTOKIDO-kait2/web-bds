<?php
class AdminPropertyController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
    }

    private function redirectToIndex($msg)
    {
        header("Location: " . BASEURL . "/adminProperty/index?msg=" . urlencode($msg));
        exit();
    }

    private function updateApproval($id, $status)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToIndex("<p class='alert alert-warning'>Yêu cầu không hợp lệ.</p>");
        }

        $propertyModel = $this->model('Property');
        if ($propertyModel->updateApprovalStatus($id, $status)) {
            $msg = $status === 'approved'
                ? "<p class='alert alert-success'>Đã phê duyệt bài đăng.</p>"
                : "<p class='alert alert-info'>Đã từ chối bài đăng.</p>";
        } else {
            $msg = $status === 'approved'
                ? "<p class='alert alert-warning'>Không thể phê duyệt bài đăng.</p>"
                : "<p class='alert alert-warning'>Không thể từ chối bài đăng.</p>";
        }

        $this->redirectToIndex($msg);
    }

    public function index()
    {
        $propertyModel = $this->model('Property');
        $data = [
            'properties' => $propertyModel->getAllPropertiesAdmin(),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : ''
        ];
        $this->view('admin/property/property_list', $data);
    }

    public function add()
    {
        $propertyModel = $this->model('Property');
        $userModel = $this->model('User');
        $locationModel = $this->model('Location');
        $data = [
            'msg' => '',
            'error' => '',
            'users' => $userModel->getUsersForDropdown(),
            'propertyTypes' => $propertyModel->getPropertyTypes(true),
            'cities' => $locationModel->getAllCities(),
            'wards' => $locationModel->getAllWards()
        ];

        if (isset($_POST['add'])) {
            if ($propertyModel->adminAddProperty($_POST, $_FILES)) {
                $data['msg'] = "<p class='alert alert-success'>Thêm bất động sản thành công!</p>";
                $data['users'] = $userModel->getUsersForDropdown();
                $data['propertyTypes'] = $propertyModel->getPropertyTypes(true);
                $data['cities'] = $locationModel->getAllCities();
                $data['wards'] = $locationModel->getAllWards();
            } else {
                $data['error'] = "<p class='alert alert-warning'>Có lỗi xảy ra khi thêm.</p>";
            }
        }

        $this->view('admin/property/property_add', $data);
    }

    public function edit($id)
    {
        $propertyModel = $this->model('Property');
        $userModel = $this->model('User');
        $locationModel = $this->model('Location');

        $data = [
            'msg' => '',
            'error' => '',
            'property' => $propertyModel->getPropertyById($id),
            'users' => $userModel->getUsersForDropdown(),
            'propertyTypes' => $propertyModel->getPropertyTypes(true),
            'cities' => $locationModel->getAllCities(),
            'wards' => $locationModel->getAllWards()
        ];

        if (isset($_POST['update'])) {
            if ($propertyModel->adminUpdateProperty($id, $_POST, $_FILES)) {
                $data['msg'] = "<p class='alert alert-success'>Cập nhật bất động sản thành công!</p>";
                $data['property'] = $propertyModel->getPropertyById($id);
            } else {
                $data['error'] = "<p class='alert alert-warning'>Có lỗi xảy ra khi cập nhật.</p>";
            }
        }

        $this->view('admin/property/property_edit', $data);
    }

    public function approve($id)
    {
        $this->updateApproval($id, 'approved');
    }

    public function reject($id)
    {
        $this->updateApproval($id, 'rejected');
    }

    public function delete($id)
    {
        $propertyModel = $this->model('Property');
        if ($propertyModel->adminDeleteProperty($id)) {
            $msg = "<p class='alert alert-success'>Đã xóa bất động sản thành công</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể xóa bất động sản</p>";
        }
        $this->redirectToIndex($msg);
    }
}
