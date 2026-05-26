<?php
class AdminFeedbackController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
    }

    private function feedbackModel()
    {
        return $this->model('Feedback');
    }

    private function redirectWithMessage($msg)
    {
        header("Location: " . BASEURL . "/adminFeedback/index?msg=" . urlencode($msg));
        exit();
    }

    public function index()
    {
        $feedbackModel = $this->feedbackModel();
        $data = [
            'feedbacks' => $feedbackModel->getAllFeedback(),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : ''
        ];
        $this->view('admin/feedback/feedback_list', $data);
    }

    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['status'])) {
            $this->redirectWithMessage("<p class='alert alert-warning'>Yêu cầu không hợp lệ</p>");
        }

        $status = (int) $_POST['status'];
        if (!in_array($status, [0, 1], true)) {
            $this->redirectWithMessage("<p class='alert alert-warning'>Trạng thái phản hồi không hợp lệ</p>");
        }

        $feedbackModel = $this->feedbackModel();
        if ($feedbackModel->updateFeedbackStatus($id, $status)) {
            $msg = $status === 1
                ? "<p class='alert alert-success'>Đã duyệt phản hồi</p>"
                : "<p class='alert alert-info'>Đã ẩn phản hồi</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể cập nhật trạng thái phản hồi</p>";
        }

        $this->redirectWithMessage($msg);
    }

    public function approve($id)
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['status'] = 1;
        $this->updateStatus($id);
    }

    public function reject($id)
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['status'] = 0;
        $this->updateStatus($id);
    }

    public function delete($id)
    {
        $feedbackModel = $this->feedbackModel();
        if ($feedbackModel->deleteFeedback($id)) {
            $msg = "<p class='alert alert-success'>Đã xóa phản hồi</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể xóa phản hồi</p>";
        }

        $this->redirectWithMessage($msg);
    }
}
