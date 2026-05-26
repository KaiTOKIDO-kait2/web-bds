<?php
class AdminTransactionController extends Controller {
    private $propertyModel;

    public function __construct() {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
        $this->propertyModel = $this->model('Property');
    }

    public function index() {
        $workflowOptions = $this->propertyModel->getInquiryWorkflowOptions();
        $filters = [];

        if (!empty($_GET['status']) && in_array($_GET['status'], $workflowOptions['status'], true)) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['case_status']) && in_array($_GET['case_status'], $workflowOptions['case_status'], true)) {
            $filters['case_status'] = $_GET['case_status'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = trim((string) $_GET['search']);
        }

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        $transactions = $this->propertyModel->getAdminTransactionList($filters);
        $totalCount = $this->propertyModel->countAdminTransactions($filters);
        $totalPages = (int) ceil($totalCount / $limit);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        $data = [
            'transactions' => $transactions,
            'workflowOptions' => $workflowOptions,
            'filters' => $filters,
            'totalCount' => $totalCount,
            'page' => $page,
            'totalPages' => $totalPages,
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
        ];

        $this->view('admin/transaction/transaction_list', $data);
    }

    public function detail($id) {
        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryById($id);

        if (empty($inquiry)) {
            $msg = "<p class='alert alert-warning'>Giao dịch không tồn tại</p>";
            header("Location: " . BASEURL . "/adminTransaction/index?msg=" . urlencode($msg));
            exit();
        }

        $data = [
            'inquiry' => $inquiry,
            'workflowOptions' => $this->propertyModel->getInquiryWorkflowOptions(),
            'logs' => $this->propertyModel->getInquiryLogs($id, 100),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
        ];

        $this->view('admin/transaction/transaction_detail', $data);
    }

    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminTransaction/index");
            exit();
        }

        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryById($id);
        if (empty($inquiry)) {
            $msg = "<p class='alert alert-warning'>Giao dịch không tồn tại</p>";
            header("Location: " . BASEURL . "/adminTransaction/index?msg=" . urlencode($msg));
            exit();
        }

        $options = $this->propertyModel->getInquiryWorkflowOptions();
        $payload = [];

        if (!empty($_POST['status']) && in_array($_POST['status'], $options['status'], true)) {
            $payload['status'] = $_POST['status'];
        }
        if (!empty($_POST['case_status']) && in_array($_POST['case_status'], $options['case_status'], true)) {
            $payload['case_status'] = $_POST['case_status'];
        }

        if (empty($payload)) {
            $msg = "<p class='alert alert-warning'>Không có dữ liệu hợp lệ để cập nhật</p>";
            header("Location: " . BASEURL . "/adminTransaction/detail/{$id}?msg=" . urlencode($msg));
            exit();
        }

        $updated = $this->propertyModel->updateInquiryWorkflow($id, $payload, [
            'actor_type' => 'admin',
            'actor_id' => isset($_SESSION['aid']) ? (int) $_SESSION['aid'] : null,
            'actor_name' => isset($_SESSION['auser']) ? (string) $_SESSION['auser'] : 'admin'
        ]);

        $msg = $updated
            ? "<p class='alert alert-success'>Đã cập nhật trạng thái giao dịch</p>"
            : "<p class='alert alert-danger'>Không thể cập nhật trạng thái giao dịch</p>";

        header("Location: " . BASEURL . "/adminTransaction/detail/{$id}?msg=" . urlencode($msg));
        exit();
    }
}
