<?php
class AdminAboutController extends Controller
{
    private $aboutModel;

    public function __construct()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
        $this->aboutModel = $this->model('About');
    }

    public function index()
    {
        $data = ['about_list' => [], 'msg' => '', 'error' => ''];

        // Xử lý thêm mới
        if (isset($_POST['addabout'])) {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $aimage = $_FILES['aimage']['name'];
            $temp_name = $_FILES['aimage']['tmp_name'];

            if (!empty($title) && !empty($aimage)) {
                $fname = time() . '_' . basename($aimage);
                move_uploaded_file($temp_name, "../admin/upload/" . $fname);

                if ($this->aboutModel->addAbout($title, $content, $fname)) {
                    $data['msg'] = "<p class='alert alert-success'>Thêm nội dung giới thiệu thành công!</p>";
                } else {
                    $data['error'] = "<p class='alert alert-warning'>Có lỗi xảy ra khi lưu vào database.</p>";
                }
            } else {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng điền tiêu đề và chọn ảnh.</p>";
            }
        }

        $data['about_list'] = $this->aboutModel->getAboutList();

        if (isset($_GET['msg'])) {
            $data['msg'] = urldecode($_GET['msg']);
        }

        $this->view('admin/about/about_index', $data);
    }

    public function edit($id)
    {
        $data = ['about' => [], 'msg' => '', 'error' => ''];

        // Load dữ liệu cũ
        $data['about'] = $this->aboutModel->getAboutById($id);

        if (!$data['about']) {
            header("Location: " . BASEURL . "/adminAbout/index");
            exit();
        }

        // Xử lý cập nhật
        if (isset($_POST['update'])) {
            $title = $_POST['utitle'];
            $content = $_POST['ucontent'];
            $aimage = $_FILES['aimage']['name'];
            $temp_name = $_FILES['aimage']['tmp_name'];

            $fname = ""; // rỗng nghĩa là không update ảnh

            if (!empty($aimage)) {
                $fname = time() . '_' . basename($aimage);
                move_uploaded_file($temp_name, "../admin/upload/" . $fname);
            }

            if ($this->aboutModel->updateAbout($id, $title, $content, $fname)) {
                $data['msg'] = "<p class='alert alert-success'>Cập nhật thành công!</p>";
                // Reload data
                $data['about'] = $this->aboutModel->getAboutById($id);
            } else {
                $data['error'] = "<p class='alert alert-warning'>Có lỗi xảy ra khi cập nhật.</p>";
            }
        }

        $this->view('admin/about/about_edit', $data);
    }

    public function delete($id)
    {
        if ($this->aboutModel->deleteAbout($id)) {
            $msg = urlencode("<p class='alert alert-success'>Đã xóa nội dung giới thiệu.</p>");
        } else {
            $msg = urlencode("<p class='alert alert-warning'>Không thể xóa.</p>");
        }
        header("Location: " . BASEURL . "/adminAbout/index?msg=$msg");
        exit();
    }
}
