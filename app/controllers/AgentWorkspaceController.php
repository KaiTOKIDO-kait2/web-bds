<?php
class AgentWorkspaceController extends Controller {
    private $propertyModel;

    public function __construct() {
        $this->propertyModel = $this->model('Property');
    }

    private function requirePostingUser() {
        if (!isset($_SESSION['uemail']) || !isset($_SESSION['uid'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->getUserById((int) $_SESSION['uid']);
        $userType = strtolower((string) ($user['utype'] ?? ($_SESSION['utype'] ?? '')));

        if (!in_array($userType, ['owner', 'agent'], true)) {
            $msg = "<p class='alert alert-warning'>Tài khoản này không có quyền truy cập khu vực quản lý đăng tin.</p>";
            header('Location: ' . BASEURL . '/home/index?msg=' . urlencode($msg));
            exit;
        }

        $_SESSION['utype'] = $userType;

        return [
            'user' => $user,
            'userType' => $userType,
        ];
    }

    private function normalizeSection($section) {
        $allowed = ['overview', 'posts', 'users', 'leads', 'transactions', 'appointments', 'create'];
        $section = strtolower(trim((string) $section));
        return in_array($section, $allowed, true) ? $section : 'overview';
    }

    private function getWorkflowOptions() {
        return $this->propertyModel->getInquiryWorkflowOptions();
    }

    private function getWorkflowActor() {
        $name = isset($_SESSION['uname']) && trim((string) $_SESSION['uname']) !== ''
            ? (string) $_SESSION['uname']
            : ((isset($_SESSION['uemail']) && trim((string) $_SESSION['uemail']) !== '') ? (string) $_SESSION['uemail'] : 'agent');

        return [
            'actor_type' => 'agent',
            'actor_id' => isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null,
            'actor_name' => $name,
        ];
    }

    private function getAgentInquiryOrRedirect($id) {
        $id = (int) $id;
        $inquiry = $this->propertyModel->getInquiryByIdForAgent($id, (int) $_SESSION['uid']);
        if (!empty($inquiry)) {
            return $inquiry;
        }

        $msg = "<p class='alert alert-warning'>Lead không tồn tại hoặc không thuộc quyền xử lý của bạn.</p>";
        header('Location: ' . BASEURL . '/agentWorkspace/index?section=leads&msg=' . urlencode($msg));
        exit;
    }

    private function buildWorkflowActionPayload($inquiry, $actionKey) {
        $actionKey = strtolower(trim((string) $actionKey));
        $payload = [];

        $status = strtolower((string) ($inquiry['status'] ?? 'pending'));
        $caseStatus = strtolower((string) ($inquiry['case_status'] ?? 'new'));
        $appointmentStatus = strtolower((string) ($inquiry['appointment_status'] ?? 'none'));

        switch ($actionKey) {
            case 'accept_lead':
                if ($status !== 'pending') {
                    return [];
                }
                $payload = [
                    'status' => 'accepted',
                    'case_status' => 'contacted',
                    'contacted_at' => date('Y-m-d H:i:s'),
                ];
                break;
            case 'reject_lead':
                if ($status !== 'pending') {
                    return [];
                }
                $payload = [
                    'status' => 'rejected',
                    'case_status' => 'cancelled',
                    'appointment_status' => 'cancelled',
                ];
                break;
            case 'confirm_appointment':
                if ($status !== 'accepted' || !in_array($appointmentStatus, ['pending', 'confirmed'], true)) {
                    return [];
                }
                $payload = [
                    'status' => 'accepted',
                    'appointment_status' => 'confirmed',
                    'case_status' => 'scheduled',
                    'appointment_confirmed_at' => date('Y-m-d H:i:s'),
                ];
                break;
            case 'cancel_appointment':
                if ($status !== 'accepted' || !in_array($appointmentStatus, ['pending', 'confirmed'], true)) {
                    return [];
                }
                $payload = [
                    'appointment_status' => 'cancelled',
                    'case_status' => 'contacted',
                ];
                break;
            case 'mark_viewed':
                if ($status !== 'accepted' || !in_array($appointmentStatus, ['confirmed', 'completed'], true)) {
                    return [];
                }
                $payload = [
                    'appointment_status' => 'completed',
                    'case_status' => 'viewed',
                    'viewed_at' => date('Y-m-d H:i:s'),
                ];
                break;
            case 'mark_completed':
                if ($status !== 'accepted' || !in_array($caseStatus, ['viewed', 'scheduled'], true)) {
                    return [];
                }
                $payload = [
                    'status' => 'accepted',
                    'case_status' => 'completed',
                ];
                break;
            case 'mark_cancelled':
                if (!in_array($status, ['accepted', 'pending'], true)) {
                    return [];
                }
                $payload = [
                    'status' => 'rejected',
                    'case_status' => 'cancelled',
                ];
                break;
        }

        return $payload;
    }

    public function index() {
        $auth = $this->requirePostingUser();
        $section = $this->normalizeSection($_GET['section'] ?? 'overview');

        if ($section === 'create') {
            header('Location: ' . BASEURL . '/property/create');
            exit;
        }

        $propertyModel = $this->propertyModel;
        $properties = $propertyModel->getPropertiesByUser((int) $_SESSION['uid']);
        $inquiries = in_array($auth['userType'], ['owner', 'agent'], true)
            ? $propertyModel->getInquiriesByAgent((int) $_SESSION['uid'])
            : [];

        $stats = [
            'total_posts' => 0,
            'approved_posts' => 0,
            'pending_posts' => 0,
            'rejected_posts' => 0,
            'total_contacts' => count($inquiries),
            'unique_contacts' => 0,
            'new_leads' => 0,
            'contacted_leads' => 0,
            'scheduled_leads' => 0,
            'viewed_leads' => 0,
            'completed_leads' => 0,
            'cancelled_leads' => 0,
            'scheduled_appointments' => 0,
        ];

        foreach ($properties as $property) {
            $stats['total_posts']++;
            $approvalStatus = strtolower((string) ($property['approval_status'] ?? 'approved'));
            if ($approvalStatus === 'approved') {
                $stats['approved_posts']++;
            } elseif ($approvalStatus === 'rejected') {
                $stats['rejected_posts']++;
            } else {
                $stats['pending_posts']++;
            }
        }

        $uniqueContactMap = [];
        foreach ($inquiries as $inquiry) {
            $key = strtolower(trim((string) ($inquiry['work_email'] ?? '')));
            if ($key === '') {
                $key = 'phone:' . trim((string) ($inquiry['phone'] ?? ''));
            }
            if ($key !== '' && !isset($uniqueContactMap[$key])) {
                $uniqueContactMap[$key] = true;
            }

            $caseStatus = strtolower((string) ($inquiry['case_status'] ?? 'new'));
            if ($caseStatus === 'contacted') {
                $stats['contacted_leads']++;
            } elseif ($caseStatus === 'scheduled') {
                $stats['scheduled_leads']++;
            } elseif ($caseStatus === 'viewed') {
                $stats['viewed_leads']++;
            } elseif ($caseStatus === 'completed') {
                $stats['completed_leads']++;
            } elseif ($caseStatus === 'cancelled') {
                $stats['cancelled_leads']++;
            } else {
                $stats['new_leads']++;
            }

            $appointmentStatus = strtolower((string) ($inquiry['appointment_status'] ?? 'none'));
            if (in_array($appointmentStatus, ['pending', 'confirmed', 'completed'], true)) {
                $stats['scheduled_appointments']++;
            }
        }
        $stats['unique_contacts'] = count($uniqueContactMap);

        $today = date('Y-m-d');
        $newLeadsToday = 0;
        $consultingTransactions = 0;
        $closedTransactions = 0;
        $cancelledTransactions = 0;

        $leadTrendMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} day"));
            $leadTrendMap[$day] = 0;
        }

        $sortedInquiries = $inquiries;
        usort($sortedInquiries, function ($a, $b) {
            $aTime = strtotime((string) ($a['created_at'] ?? $a['date'] ?? '1970-01-01 00:00:00'));
            $bTime = strtotime((string) ($b['created_at'] ?? $b['date'] ?? '1970-01-01 00:00:00'));
            return $bTime <=> $aTime;
        });

        foreach ($inquiries as $inquiry) {
            $createdAt = (string) ($inquiry['created_at'] ?? $inquiry['date'] ?? '');
            $createdDate = $createdAt !== '' ? date('Y-m-d', strtotime($createdAt)) : '';

            if ($createdDate === $today) {
                $newLeadsToday++;
            }

            if ($createdDate !== '' && isset($leadTrendMap[$createdDate])) {
                $leadTrendMap[$createdDate]++;
            }

            $caseStatus = strtolower((string) ($inquiry['case_status'] ?? 'new'));
            if (in_array($caseStatus, ['contacted', 'scheduled'], true)) {
                $consultingTransactions++;
            } elseif ($caseStatus === 'completed') {
                $closedTransactions++;
            } elseif ($caseStatus === 'cancelled') {
                $cancelledTransactions++;
            }
        }

        $totalLeads = count($inquiries);
        $conversionRate = $totalLeads > 0 ? round(($closedTransactions / $totalLeads) * 100, 1) : 0;

        $leadTrend = [];
        foreach ($leadTrendMap as $date => $count) {
            $leadTrend[] = [
                'date' => $date,
                'label' => date('D', strtotime($date)),
                'count' => (int) $count,
            ];
        }

        $recentLeads = array_slice($sortedInquiries, 0, 5);
        $recentTransactions = [];
        foreach ($sortedInquiries as $inquiry) {
            $caseStatus = strtolower((string) ($inquiry['case_status'] ?? 'new'));
            if (in_array($caseStatus, ['contacted', 'scheduled', 'completed', 'cancelled'], true)) {
                $recentTransactions[] = $inquiry;
            }
            if (count($recentTransactions) >= 5) {
                break;
            }
        }

        $transactionStatus = [
            'consulting' => (int) $consultingTransactions,
            'closed' => (int) $closedTransactions,
            'cancelled' => (int) $cancelledTransactions,
        ];

        $data = [
            'section' => $section,
            'user' => $auth['user'],
            'userType' => $auth['userType'],
            'stats' => $stats,
            'properties' => $properties,
            'inquiries' => $inquiries,
            'overviewMetrics' => [
                'totalLeads' => (int) $totalLeads,
                'newLeadsToday' => (int) $newLeadsToday,
                'consultingTransactions' => (int) $consultingTransactions,
                'closedTransactions' => (int) $closedTransactions,
                'cancelledTransactions' => (int) $cancelledTransactions,
                'conversionRate' => (float) $conversionRate,
            ],
            'leadTrend' => $leadTrend,
            'transactionStatus' => $transactionStatus,
            'recentLeads' => $recentLeads,
            'recentTransactions' => $recentTransactions,
            'msg' => isset($_GET['msg']) ? urldecode((string) $_GET['msg']) : '',
            'canManageUsers' => in_array($auth['userType'], ['owner', 'agent'], true),
            'canManageLeads' => in_array($auth['userType'], ['owner', 'agent'], true),
        ];

        $this->view('agent/workspace', $data);
    }

    public function leadDetail($id = '') {
        $auth = $this->requirePostingUser();
        if (!in_array($auth['userType'], ['owner', 'agent'], true)) {
            $msg = "<p class='alert alert-warning'>Bạn không có quyền xử lý lead.</p>";
            header('Location: ' . BASEURL . '/agentWorkspace/index?msg=' . urlencode($msg));
            exit;
        }

        $inquiry = $this->getAgentInquiryOrRedirect($id);

        $data = [
            'user' => $auth['user'],
            'userType' => $auth['userType'],
            'inquiry' => $inquiry,
            'logs' => $this->propertyModel->getInquiryLogs((int) $inquiry['id'], 100),
            'workflowOptions' => $this->getWorkflowOptions(),
            'msg' => isset($_GET['msg']) ? urldecode((string) $_GET['msg']) : '',
            'propertyRented' => $this->propertyModel->getPropertyStatusById((int)($inquiry['property_id'] ?? 0)) === 'rented',
            'peerContacts' => $this->propertyModel->getInquiryPeersByProperty($inquiry['property_id'] ?? 0, null, 50),
        ];

        $this->view('agent/inquiry_detail', $data);
    }

    public function updateLeadWorkflow($id = '') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/agentWorkspace/index?section=leads');
            exit;
        }

        $auth = $this->requirePostingUser();
        if (!in_array($auth['userType'], ['owner', 'agent'], true)) {
            $msg = "<p class='alert alert-warning'>Bạn không có quyền xử lý lead.</p>";
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xử lý lead.']);
                exit;
            }
            header('Location: ' . BASEURL . '/agentWorkspace/index?section=leads&msg=' . urlencode($msg));
            exit;
        }

        $inquiry = $this->getAgentInquiryOrRedirect($id);
        $actionKey = isset($_POST['action_key']) ? (string) $_POST['action_key'] : '';

        $cancelActions = ['reject_lead', 'mark_cancelled'];
        if (!in_array($actionKey, $cancelActions, true)) {
            $propStatus = $this->propertyModel->getPropertyStatusById((int)($inquiry['property_id'] ?? 0));
            if ($propStatus === 'rented') {
                $blockedMsg = 'BĐS này đã được cho thuê, không thể tiếp tục thao tác.';
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $blockedMsg]);
                    exit;
                }
                header('Location: ' . BASEURL . '/agentWorkspace/leadDetail/' . (int) $inquiry['id'] . '?msg=' . urlencode("<p class='alert alert-warning'>" . $blockedMsg . '</p>'));
                exit;
            }
        }

        $payload = $this->buildWorkflowActionPayload($inquiry, $actionKey);

        // Handle appointment_date for confirm_appointment action
        if ($actionKey === 'confirm_appointment' && isset($_POST['appointment_date'])) {
            $appointmentDate = trim((string) $_POST['appointment_date']);
            if ($appointmentDate !== '') {
                // Convert datetime-local format to Y-m-d H:i:s
                $payload['appointment_requested_at'] = str_replace('T', ' ', $appointmentDate) . ':00';
            }
        }

        $brokerNote = isset($_POST['broker_note']) ? trim((string) $_POST['broker_note']) : '';
        if ($brokerNote !== '') {
            $noteField = in_array($actionKey, ['mark_completed', 'mark_cancelled'], true)
                ? 'result_note'
                : 'appointment_note';
            $existing = !empty($inquiry[$noteField]) ? (string) $inquiry[$noteField] : '';
            $entry = '[Broker ' . date('d-m-Y H:i:s') . '] ' . $brokerNote;
            $payload[$noteField] = $existing !== '' ? ($existing . "\n\n" . $entry) : $entry;
        }

        if (empty($payload)) {
            $msg = "<p class='alert alert-warning'>Hành động workflow không hợp lệ.</p>";
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Hành động workflow không hợp lệ.']);
                exit;
            }
            header('Location: ' . BASEURL . '/agentWorkspace/leadDetail/' . (int) $inquiry['id'] . '?msg=' . urlencode($msg));
            exit;
        }

        $updated = $this->propertyModel->updateInquiryWorkflow((int) $inquiry['id'], $payload, $this->getWorkflowActor());
        $msg = $updated
            ? "Cập nhật workflow lead thành công."
            : "Không thể cập nhật workflow lead.";

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $updated, 'message' => $msg]);
            exit;
        }

        header('Location: ' . BASEURL . '/agentWorkspace/leadDetail/' . (int) $inquiry['id'] . '?msg=' . urlencode("<p class='alert alert-" . ($updated ? 'success' : 'danger') . "'>" . $msg . "</p>"));
        exit;
    }

    public function addLeadNote($id = '') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/agentWorkspace/index?section=leads');
            exit;
        }

        $auth = $this->requirePostingUser();
        if (!in_array($auth['userType'], ['owner', 'agent'], true)) {
            $msg = "<p class='alert alert-warning'>Bạn không có quyền xử lý lead.</p>";
            header('Location: ' . BASEURL . '/agentWorkspace/index?section=leads&msg=' . urlencode($msg));
            exit;
        }

        $inquiry = $this->getAgentInquiryOrRedirect($id);
        $note = isset($_POST['notes']) ? trim((string) $_POST['notes']) : '';
        if ($note === '') {
            $msg = "<p class='alert alert-warning'>Vui lòng nhập ghi chú.</p>";
            header('Location: ' . BASEURL . '/agentWorkspace/leadDetail/' . (int) $inquiry['id'] . '?msg=' . urlencode($msg));
            exit;
        }

        $existingNotes = !empty($inquiry['notes']) ? (string) $inquiry['notes'] : '';
        $newNote = '[' . date('d-m-Y H:i:s') . '] ' . $note;
        $allNotes = $existingNotes !== '' ? ($existingNotes . "\n\n" . $newNote) : $newNote;

        $updated = $this->propertyModel->updateInquiryWorkflow((int) $inquiry['id'], ['notes' => $allNotes], $this->getWorkflowActor());
        $msg = $updated
            ? "<p class='alert alert-success'>Đã thêm ghi chú lead.</p>"
            : "<p class='alert alert-danger'>Không thể thêm ghi chú lead.</p>";

        header('Location: ' . BASEURL . '/agentWorkspace/leadDetail/' . (int) $inquiry['id'] . '?msg=' . urlencode($msg));
        exit;
    }
}
