<?php
class AdminLocationController extends Controller {
    private $locationModel;

    public function __construct() {
        if(!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }
        $this->locationModel = $this->model('Location');
    }

    public function ward() {
        $data = [
            'wards' => [],
            'cities' => [],
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
            'error' => ''
        ];

        if(isset($_POST['insert'])) {
            $wardName = trim($_POST['ward'] ?? '');
            $cityId = isset($_POST['city_id']) ? (int)$_POST['city_id'] : 0;

            if($wardName !== '' && $cityId > 0) {
                if($this->locationModel->addWard($wardName, $cityId, null)) {
                    $data['msg'] = "<p class='alert alert-success'>Thêm phường/xã thành công</p>";
                } else {
                    $data['error'] = "<p class='alert alert-warning'>Không thể thêm phường/xã</p>";
                }
            } else {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng điền đầy đủ thông tin</p>";
            }
        }

        $data['wards'] = $this->locationModel->getAllWards();
        $data['cities'] = $this->locationModel->getAllCities();
        $this->view('admin/location/ward', $data);
    }

    public function wardEdit($id) {
        $data = [
            'ward' => null,
            'cities' => [],
            'msg' => '',
            'error' => ''
        ];

        if(isset($_POST['update'])) {
            $wardName = trim($_POST['ward'] ?? '');
            $cityId = isset($_POST['city_id']) ? (int)$_POST['city_id'] : 0;

            if($wardName !== '' && $cityId > 0) {
                if($this->locationModel->updateWard($id, $wardName, $cityId, null)) {
                    $data['msg'] = "<p class='alert alert-success'>Cập nhật phường/xã thành công</p>";
                } else {
                    $data['error'] = "<p class='alert alert-warning'>Không thể cập nhật phường/xã</p>";
                }
            } else {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng điền đầy đủ thông tin</p>";
            }
        }

        $data['cities'] = $this->locationModel->getAllCities();
        $data['ward'] = $this->locationModel->getWardById($id);

        $this->view('admin/location/ward_edit', $data);
    }

    public function wardDelete($id) {
        if($this->locationModel->deleteWard($id)) {
            $msg = "<p class='alert alert-success'>Đã xóa phường/xã</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể xóa phường/xã</p>";
        }
        header("Location: " . BASEURL . "/adminLocation/ward?msg=" . urlencode($msg));
        exit();
    }

    public function city() {
        $data = [
            'cities' => [],
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
            'error' => ''
        ];

        // Xử lý POST thêm city (thành phố)
        if(isset($_POST['insert'])) {
            $cityName = trim($_POST['city']);
            if(!empty($cityName)) {
                if($this->locationModel->addCity($cityName, null)) {
                    $data['msg'] = "<p class='alert alert-success'>Thêm thành phố thành công</p>";
                } else {
                    $data['error'] = "<p class='alert alert-warning'>Không thể thêm thành phố</p>";
                }
            } else {
                $data['error'] = "<p class='alert alert-warning'>Vui lòng điền đầy đủ thông tin</p>";
            }
        }

        $data['cities'] = $this->locationModel->getAllCities();
        $this->view('admin/location/city', $data);
    }

    public function cityEdit($id) {
        $data = ['city' => null, 'msg' => '', 'error' => ''];

        if(isset($_POST['update'])) {
            $cityName = trim($_POST['city']);
            if(!empty($cityName)) {
                if($this->locationModel->updateCity($id, $cityName, null)) {
                    $data['msg'] = "<p class='alert alert-success'>Cập nhật thành phố thành công</p>";
                }
            }
        }

        $data['city'] = $this->locationModel->getCityById($id);

        $this->view('admin/location/city_edit', $data);
    }

    public function cityDelete($id) {
        if($this->locationModel->deleteCity($id)) {
            $msg = "<p class='alert alert-success'>Đã xóa thành phố</p>";
        } else {
            $msg = "<p class='alert alert-warning'>Không thể xóa thành phố</p>";
        }
        header("Location: " . BASEURL . "/adminLocation/city?msg=" . urlencode($msg));
        exit();
    }
}
