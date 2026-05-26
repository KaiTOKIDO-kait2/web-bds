<?php
class PageController extends Controller {
    public function about() {
        $pageModel = $this->model('Page');
        $userModel = $this->model('User');
        $leaders = $userModel->getAgents();
        $data = [
            'aboutData' => $pageModel->getAboutContent(),
            'leadership' => is_array($leaders) ? array_slice($leaders, 0, 3) : []
        ];
        $this->view('page/about', $data);
    }

    public function contact() {
        $data = [
            'msg' => '',
            'error' => '',
            'formData' => []
        ];

        if(isset($_POST['send'])) {
            // Trim and sanitize inputs
            $postData = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'subject' => trim($_POST['subject'] ?? 'Yêu cầu liên hệ'),
                'message' => trim($_POST['message'] ?? ''),
                'user_type' => trim($_POST['user_type'] ?? 'buyer')
            ];

            // Store form data for repopulation
            $data['formData'] = $postData;

            // Validation
            $errors = [];
            if(empty($postData['name'])) $errors[] = 'Vui lòng nhập tên';
            if(empty($postData['email']) || !filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
            if(empty($postData['phone'])) $errors[] = 'Vui lòng nhập số điện thoại';
            if(empty($postData['message']) || strlen($postData['message']) < 10) $errors[] = 'Tin nhắn phải có ít nhất 10 ký tự';

            if(empty($errors)) {
                $pageModel = $this->model('Page');
                if($pageModel->saveContactMessage($postData)) {
                    $data['msg'] = 'Yêu cầu của bạn đã được gửi thành công. Chúng tôi sẽ liên hệ với bạn sớm nhất.';
                    $data['formData'] = []; // Clear form on success
                } else {
                    $data['error'] = 'Gửi tin nhắn không thành công. Vui lòng thử lại.';
                }
            } else {
                $data['error'] = implode(' | ', $errors);
            }
        }

        $this->view('page/contact', $data);
    }

    public function calc() {
        $data = [];
        if(isset($_POST['calc'])) {
            $amount = $_POST['amount'];
            $mon = $_POST['month'];
            $int = $_POST['interest'];
            
            $interest = $amount * $int / 100;
            $pay = $amount + $interest;
            $month = $pay / $mon;

            $data = [
                'amount' => $amount,
                'mon' => $mon,
                'int' => $int,
                'interest' => $interest,
                'pay' => $pay,
                'month' => $month
            ];
        }
        $this->view('page/calc', $data);
    }
}
