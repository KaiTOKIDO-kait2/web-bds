<?php
class AdminContactController extends Controller
{
    private $contactModel;

    public function __construct()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
        $this->contactModel = $this->model('Contact');
    }

    public function index()
    {
        $data = [
            'contacts' => $this->contactModel->getAllContacts(),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : ''
        ];
        $this->view('admin/contact/contact_list', $data);
    }

    public function delete($id)
    {
        if ($this->contactModel->deleteContact($id)) {
            $msg = "<p class='alert alert-success'>Đã xóa liên hệ</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể xóa liên hệ</p>";
        }
        header("Location: " . BASEURL . "/adminContact/index?msg=" . urlencode($msg));
        exit();
    }
}
