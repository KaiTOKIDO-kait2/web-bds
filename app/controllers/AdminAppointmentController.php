<?php
class AdminAppointmentController extends Controller
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
        $workflowOptions = $this->propertyModel->getInquiryWorkflowOptions();
        $filters = [];

        if (!empty($_GET['appointment_status']) && in_array($_GET['appointment_status'], $workflowOptions['appointment_status'], true)) {
            $filters['appointment_status'] = $_GET['appointment_status'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = trim((string) $_GET['search']);
        }
        if (!empty($_GET['date_from'])) {
            $d = trim((string) $_GET['date_from']);
            if (strtotime($d) !== false) {
                $filters['date_from'] = date('Y-m-d', strtotime($d));
            }
        }
        if (!empty($_GET['date_to'])) {
            $d = trim((string) $_GET['date_to']);
            if (strtotime($d) !== false) {
                $filters['date_to'] = date('Y-m-d', strtotime($d));
            }
        }

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        $appointments = $this->propertyModel->getAdminAppointments($filters);
        $totalCount = $this->propertyModel->countAdminAppointments($filters);
        $totalPages = (int) ceil($totalCount / $limit);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        $data = [
            'appointments' => $appointments,
            'workflowOptions' => $workflowOptions,
            'filters' => $filters,
            'totalCount' => $totalCount,
            'page' => $page,
            'totalPages' => $totalPages,
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
        ];

        $this->view('admin/appointment/appointment_list', $data);
    }
}
