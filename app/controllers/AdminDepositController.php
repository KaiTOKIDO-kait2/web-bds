<?php
class AdminDepositController extends Controller
{
    private $propertyModel;

    public function __construct()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
        $this->propertyModel = $this->model('Property');
    }

    public function index()
    {
        $msg = '<p class="alert alert-warning">Module đặt cọc đã bị vô hiệu hóa trong workflow mới.</p>';
        header("Location: " . BASEURL . "/adminTransaction/index?msg=" . urlencode($msg));
        exit();
    }
}
