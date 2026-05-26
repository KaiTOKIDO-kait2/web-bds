<?php
class UserWorkspaceController extends Controller
{
    private function getWorkflowActor()
    {
        $name = isset($_SESSION['uname']) && trim((string) $_SESSION['uname']) !== ''
            ? (string) $_SESSION['uname']
            : ((isset($_SESSION['uemail']) && trim((string) $_SESSION['uemail']) !== '') ? (string) $_SESSION['uemail'] : 'user');

        return [
            'actor_type' => 'user',
            'actor_id' => isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null,
            'actor_name' => $name,
        ];
    }

    private function getUserInquiryOrRedirect($id)
    {
        $id = (int) $id;
        $request = $this->model('Property')->getInquiryByIdForInquirer($id, (int) $_SESSION['uid']);
        if (!empty($request)) {
            return $request;
        }

        $msg = '<p class="alert alert-warning">Yêu cầu không tồn tại hoặc không thuộc tài khoản của bạn.</p>';
        header('Location: ' . BASEURL . '/userWorkspace/index?msg=' . urlencode($msg));
        exit;
    }

    private function requireLoggedInUser()
    {
        if (!isset($_SESSION['uemail']) || !isset($_SESSION['uid'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->getUserById((int) $_SESSION['uid']);
        $userType = strtolower((string) ($user['utype'] ?? ($_SESSION['utype'] ?? '')));
        $_SESSION['utype'] = $userType;

        if (in_array($userType, ['owner', 'agent'], true)) {
            $msg = '<p class="alert alert-warning">Tài khoản có quyền đăng tin vui lòng sử dụng khu vực Agent Workspace.</p>';
            header('Location: ' . BASEURL . '/agentWorkspace/index?section=overview&msg=' . urlencode($msg));
            exit;
        }

        return [
            'user' => $user,
            'userType' => $userType,
        ];
    }

    private function normalizeSection($section)
    {
        $allowed = ['overview', 'requests', 'appointments'];
        $section = strtolower(trim((string) $section));
        return in_array($section, $allowed, true) ? $section : 'overview';
    }

    public function index()
    {
        $auth = $this->requireLoggedInUser();
        $section = $this->normalizeSection($_GET['section'] ?? 'overview');

        $propertyModel = $this->model('Property');
        $requests = $propertyModel->getInquiriesByInquirer((int) $_SESSION['uid'], 100);

        $stats = [
            'total_requests' => count($requests),
            'pending_requests' => 0,
            'accepted_requests' => 0,
            'rejected_requests' => 0,
            'scheduled_appointments' => 0,
            'completed_cases' => 0,
        ];

        foreach ($requests as $request) {
            $status = strtolower((string) ($request['status'] ?? 'pending'));
            if ($status === 'accepted') {
                $stats['accepted_requests']++;
            } elseif ($status === 'rejected') {
                $stats['rejected_requests']++;
            } else {
                $stats['pending_requests']++;
            }

            $appointmentStatus = strtolower((string) ($request['appointment_status'] ?? 'none'));
            if (in_array($appointmentStatus, ['pending', 'confirmed', 'completed'], true)) {
                $stats['scheduled_appointments']++;
            }

            if (($request['case_status'] ?? '') === 'completed') {
                $stats['completed_cases']++;
            }
        }

        $data = [
            'section' => $section,
            'user' => $auth['user'],
            'userType' => $auth['userType'],
            'stats' => $stats,
            'requests' => $requests,
            'msg' => isset($_GET['msg']) ? urldecode((string) $_GET['msg']) : '',
        ];

        $this->view('user/workspace', $data);
    }

    public function requestDetail($id = '')
    {
        $auth = $this->requireLoggedInUser();
        $id = (int) $id;

        $request = $this->getUserInquiryOrRedirect($id);

        $propertyModel = $this->model('Property');

        $data = [
            'user' => $auth['user'],
            'userType' => $auth['userType'],
            'request' => $request,
            'logs' => $propertyModel->getInquiryLogs($id, 100),
            'busySlots' => $propertyModel->getAgentBusyAppointmentSlots($request['agent_uid'] ?? 0, $request['id'] ?? null),
            'peerContacts' => $propertyModel->getInquiryPeersByProperty($request['property_id'] ?? 0, $request['id'] ?? null, 50),
            'msg' => isset($_GET['msg']) ? urldecode((string) $_GET['msg']) : '',
        ];

        $this->view('user/request_detail', $data);
    }

    public function submitAppointment($id = '')
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/userWorkspace/requestDetail/' . (int) $id);
            exit;
        }

        $this->requireLoggedInUser();
        $request = $this->getUserInquiryOrRedirect($id);
        $propertyModel = $this->model('Property');

        $appointmentAt = isset($_POST['appointment_at']) ? trim((string) $_POST['appointment_at']) : '';
        $appointmentNote = isset($_POST['appointment_note']) ? trim((string) $_POST['appointment_note']) : '';

        if ($appointmentAt === '') {
            $msg = '<p class="alert alert-warning">Vui lòng chọn thời gian hẹn.</p>';
            header('Location: ' . BASEURL . '/userWorkspace/requestDetail/' . (int) $request['id'] . '?msg=' . urlencode($msg));
            exit;
        }

        $agentUid = isset($request['agent_uid']) ? (int) $request['agent_uid'] : 0;
        if ($agentUid <= 0) {
            $msg = '<p class="alert alert-danger">Không xác định được môi giới để đặt lịch. Vui lòng liên hệ hỗ trợ.</p>';
            header('Location: ' . BASEURL . '/userWorkspace/requestDetail/' . (int) $request['id'] . '?msg=' . urlencode($msg));
            exit;
        }

        if ($propertyModel->hasAppointmentConflict($agentUid, $appointmentAt, $request['id'] ?? null)) {
            $msg = '<p class="alert alert-warning">Khung giờ này đã có lịch khác. Vui lòng chọn thời gian khác.</p>';
            header('Location: ' . BASEURL . '/userWorkspace/requestDetail/' . (int) $request['id'] . '?msg=' . urlencode($msg));
            exit;
        }

        $payload = [
            'appointment_status' => 'pending',
            'appointment_requested_at' => $appointmentAt,
        ];

        if ($appointmentNote !== '') {
            $existing = !empty($request['appointment_note']) ? (string) $request['appointment_note'] : '';
            $entry = '[User ' . date('d-m-Y H:i:s') . '] ' . $appointmentNote;
            $payload['appointment_note'] = $existing !== '' ? ($existing . "\n\n" . $entry) : $entry;
        }

        $updated = $propertyModel->updateInquiryWorkflow((int) $request['id'], $payload, $this->getWorkflowActor());
        $msg = $updated
            ? '<p class="alert alert-success">Đã gửi đề xuất lịch hẹn cho môi giới.</p>'
            : '<p class="alert alert-danger">Không thể gửi lịch hẹn. Vui lòng thử lại.</p>';

        header('Location: ' . BASEURL . '/userWorkspace/requestDetail/' . (int) $request['id'] . '?msg=' . urlencode($msg));
        exit;
    }

    public function submitDeposit($id = '')
    {
        $msg = '<p class="alert alert-warning">Chức năng đặt cọc đã được loại bỏ khỏi workflow mới.</p>';
        header('Location: ' . BASEURL . '/userWorkspace/requestDetail/' . (int) $id . '?msg=' . urlencode($msg));
        exit;
    }
}
