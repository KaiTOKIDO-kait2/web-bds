<?php
class AdminInquiryController extends Controller
{
    private $propertyModel;

    private function getWorkflowOptions()
    {
        return $this->propertyModel->getInquiryWorkflowOptions();
    }

    private function normalizeFilterValue($key, $value)
    {
        $options = $this->getWorkflowOptions();
        if (!isset($options[$key]) || !is_array($options[$key])) {
            return '';
        }
        return in_array($value, $options[$key], true) ? $value : '';
    }

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
        $filters = [];
        $workflowOptions = $this->getWorkflowOptions();
        $validStatuses = $workflowOptions['status'];
        $validSorts = ['created_at', 'inquirer_name', 'status'];

        // Get filter parameters from query string
        if (!empty($_GET['status']) && in_array($_GET['status'], $validStatuses, true)) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['case_status'])) {
            $caseStatus = $this->normalizeFilterValue('case_status', $_GET['case_status']);
            if ($caseStatus !== '') {
                $filters['case_status'] = $caseStatus;
            }
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['date_from'])) {
            $dateFrom = trim((string) $_GET['date_from']);
            if (strtotime($dateFrom) !== false) {
                $filters['date_from'] = date('Y-m-d', strtotime($dateFrom));
            }
        }
        if (!empty($_GET['date_to'])) {
            $dateTo = trim((string) $_GET['date_to']);
            if (strtotime($dateTo) !== false) {
                $filters['date_to'] = date('Y-m-d', strtotime($dateTo));
            }
        }
        if (!empty($_GET['sort']) && in_array($_GET['sort'], $validSorts, true)) {
            $filters['sort'] = $_GET['sort'];
        }
        if (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC'], true)) {
            $filters['order'] = strtoupper($_GET['order']);
        }

        // Pagination
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        $inquiries = $this->propertyModel->getAllInquiries($filters);
        $totalCount = $this->propertyModel->countInquiries($filters);
        $summary = $this->propertyModel->getInquiryWorkflowSummary();
        $totalPages = (int) ceil($totalCount / $limit);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        $data = [
            'inquiries' => $inquiries,
            'totalCount' => $totalCount,
            'page' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit,
            'filters' => $filters,
            'workflowOptions' => $workflowOptions,
            'summary' => $summary,
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
            'statusOptions' => $validStatuses
        ];

        $this->view('admin/inquiry/inquiry_list', $data);
    }

    public function detail($id)
    {
        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryById($id);

        if (empty($inquiry)) {
            $msg = "<p class='alert alert-warning'>Liên hệ không tồn tại</p>";
            header("Location: " . BASEURL . "/adminInquiry/index?msg=" . urlencode($msg));
            exit();
        }

        $data = [
            'inquiry' => $inquiry,
            'logs' => $this->propertyModel->getInquiryLogs($id, 100),
            'workflowOptions' => $this->getWorkflowOptions(),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
            'statusOptions' => $this->getWorkflowOptions()['status']
        ];

        $this->view('admin/inquiry/inquiry_detail', $data);
    }

    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminInquiry/index");
            exit();
        }

        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryById($id);

        if (empty($inquiry)) {
            $msg = "<p class='alert alert-warning'>Liên hệ không tồn tại</p>";
            header("Location: " . BASEURL . "/adminInquiry/index?msg=" . urlencode($msg));
            exit();
        }

        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $validStatuses = $this->getWorkflowOptions()['status'];

        if (!in_array($status, $validStatuses, true)) {
            $msg = "<p class='alert alert-warning'>Trạng thái không hợp lệ</p>";
            header("Location: " . BASEURL . "/adminInquiry/detail/{$id}?msg=" . urlencode($msg));
            exit();
        }

        $updateData = ['status' => $status];

        if (
            $this->propertyModel->updateInquiryWorkflow($id, $updateData, [
                'actor_type' => 'admin',
                'actor_id' => isset($_SESSION['aid']) ? (int) $_SESSION['aid'] : null,
                'actor_name' => isset($_SESSION['auser']) ? (string) $_SESSION['auser'] : 'admin'
            ])
        ) {
            $msg = "<p class='alert alert-success'>Cập nhật trạng thái thành công</p>";
        } else {
            $msg = "<p class='alert alert-danger'>Không thể cập nhật trạng thái</p>";
        }

        header("Location: " . BASEURL . "/adminInquiry/detail/{$id}?msg=" . urlencode($msg));
        exit();
    }

    public function addNote($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminInquiry/index");
            exit();
        }

        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryById($id);

        if (empty($inquiry)) {
            $msg = "<p class='alert alert-warning'>Liên hệ không tồn tại</p>";
            header("Location: " . BASEURL . "/adminInquiry/index?msg=" . urlencode($msg));
            exit();
        }

        $note = isset($_POST['notes']) ? trim((string) $_POST['notes']) : '';

        if (empty($note)) {
            $msg = "<p class='alert alert-warning'>Vui lòng nhập ghi chú</p>";
            header("Location: " . BASEURL . "/adminInquiry/detail/{$id}?msg=" . urlencode($msg));
            exit();
        }

        // Append new note to existing notes with timestamp
        $existingNotes = !empty($inquiry['notes']) ? $inquiry['notes'] : '';
        $newNote = "[" . date('d-m-Y H:i:s') . "] " . $note;
        $allNotes = !empty($existingNotes) ? $existingNotes . "\n\n" . $newNote : $newNote;

        if (
            $this->propertyModel->updateInquiryWorkflow($id, ['notes' => $allNotes], [
                'actor_type' => 'admin',
                'actor_id' => isset($_SESSION['aid']) ? (int) $_SESSION['aid'] : null,
                'actor_name' => isset($_SESSION['auser']) ? (string) $_SESSION['auser'] : 'admin'
            ])
        ) {
            $msg = "<p class='alert alert-success'>Thêm ghi chú thành công</p>";
        } else {
            $msg = "<p class='alert alert-danger'>Không thể thêm ghi chú</p>";
        }

        header("Location: " . BASEURL . "/adminInquiry/detail/{$id}?msg=" . urlencode($msg));
        exit();
    }

    public function delete($id)
    {
        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryById($id);

        if (empty($inquiry)) {
            $msg = "<p class='alert alert-warning'>Liên hệ không tồn tại</p>";
        } else {
            if ($this->propertyModel->deleteInquiry($id)) {
                $msg = "<p class='alert alert-success'>Đã xóa liên hệ</p>";
            } else {
                $msg = "<p class='alert alert-danger'>Không thể xóa liên hệ</p>";
            }
        }

        header("Location: " . BASEURL . "/adminInquiry/index?msg=" . urlencode($msg));
        exit();
    }

    public function updateWorkflow($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminInquiry/index");
            exit();
        }

        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryById($id);
        if (empty($inquiry)) {
            $msg = "<p class='alert alert-warning'>Liên hệ không tồn tại</p>";
            header("Location: " . BASEURL . "/adminInquiry/index?msg=" . urlencode($msg));
            exit();
        }

        $options = $this->getWorkflowOptions();
        $payload = [];

        if (!empty($_POST['status']) && in_array($_POST['status'], $options['status'], true)) {
            $payload['status'] = $_POST['status'];
        }
        if (!empty($_POST['case_status']) && in_array($_POST['case_status'], $options['case_status'], true)) {
            $payload['case_status'] = $_POST['case_status'];
        }
        if (!empty($_POST['appointment_status']) && in_array($_POST['appointment_status'], $options['appointment_status'], true)) {
            $payload['appointment_status'] = $_POST['appointment_status'];
        }
        if (isset($_POST['desired_budget'])) {
            $payload['desired_budget'] = trim((string) $_POST['desired_budget']);
        }
        if (isset($_POST['desired_area'])) {
            $payload['desired_area'] = trim((string) $_POST['desired_area']);
        }
        if (isset($_POST['desired_move_in_time'])) {
            $payload['desired_move_in_time'] = trim((string) $_POST['desired_move_in_time']);
        }
        if (isset($_POST['appointment_requested_at'])) {
            $val = trim((string) $_POST['appointment_requested_at']);
            if ($val !== '') {
                // convert datetime-local (Y-m-d\TH:i) to Y-m-d H:i:s
                $val = str_replace('T', ' ', $val) . ':00';
                $payload['appointment_requested_at'] = $val;
            }
        }
        if (isset($_POST['appointment_confirmed_at'])) {
            $val = trim((string) $_POST['appointment_confirmed_at']);
            if ($val !== '') {
                $val = str_replace('T', ' ', $val) . ':00';
                $payload['appointment_confirmed_at'] = $val;
            }
        }

        if (empty($payload)) {
            $msg = "<p class='alert alert-warning'>Không có dữ liệu cần cập nhật</p>";
            header("Location: " . BASEURL . "/adminInquiry/detail/{$id}?msg=" . urlencode($msg));
            exit();
        }

        $updated = $this->propertyModel->updateInquiryWorkflow($id, $payload, [
            'actor_type' => 'admin',
            'actor_id' => isset($_SESSION['aid']) ? (int) $_SESSION['aid'] : null,
            'actor_name' => isset($_SESSION['auser']) ? (string) $_SESSION['auser'] : 'admin'
        ]);

        if ($updated) {
            $msg = "<p class='alert alert-success'>Cập nhật workflow thành công</p>";
        } else {
            $msg = "<p class='alert alert-danger'>Không thể cập nhật workflow</p>";
        }

        header("Location: " . BASEURL . "/adminInquiry/detail/{$id}?msg=" . urlencode($msg));
        exit();
    }
}
