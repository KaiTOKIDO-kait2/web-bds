<?php
class PropertyController extends Controller {
    private function getCurrentUserType() {
        if (!isset($_SESSION['uid'])) {
            return '';
        }

        if (!empty($_SESSION['utype'])) {
            return strtolower((string) $_SESSION['utype']);
        }

        $userModel = $this->model('User');
        $user = $userModel->getUserById((int) $_SESSION['uid']);
        $sessionType = strtolower((string) ($user['utype'] ?? ''));
        if ($sessionType !== '') {
            $_SESSION['utype'] = $sessionType;
        }

        return $sessionType;
    }

    private function canCurrentUserPostProperty() {
        return in_array($this->getCurrentUserType(), ['owner', 'agent'], true);
    }

    private function redirectPostingNotAllowed() {
        $msg = "<p class='alert alert-warning'>Tài khoản thuê bất động sản không có quyền đăng hoặc quản lý tin.</p>";
        header('Location: ' . BASEURL . '/home/index?msg=' . urlencode($msg));
        exit;
    }

    public function index() {
        $propertyModel = $this->model('Property');
        $locationModel = $this->model('Location');

        $type  = '';
        $stype = '';
        $city  = '';
        $cityId = 0;
        $sort  = '';
        $keyword = '';
        $priceRange = '';
        $areaRange = '';
        $rooms = '';
        $viewMode = 'grid';
        $selectedTypes = [];
        $isFiltering = false;

        if (isset($_POST['filter'])) {
            $type  = isset($_POST['type']) ? trim($_POST['type']) : '';
            $stype = isset($_POST['stype']) ? trim($_POST['stype']) : '';
            $cityId = isset($_POST['city_id']) ? (int) $_POST['city_id'] : 0;
            $sort  = isset($_POST['sort']) ? trim($_POST['sort']) : '';
            $keyword = isset($_POST['q']) ? trim($_POST['q']) : '';
            $priceRange = isset($_POST['price_range']) ? trim($_POST['price_range']) : '';
            $areaRange = isset($_POST['area_range']) ? trim($_POST['area_range']) : '';
            $rooms = isset($_POST['rooms']) ? trim($_POST['rooms']) : '';
            $viewMode = isset($_POST['view']) ? trim($_POST['view']) : 'grid';
            $selectedTypes = isset($_POST['types']) && is_array($_POST['types'])
                ? array_values(array_unique(array_filter(array_map('trim', $_POST['types']))))
                : [];
            if (!empty($selectedTypes)) {
                $type = '';
            }
            $isFiltering = true;
        } elseif (
            isset($_GET['type']) || isset($_GET['stype']) || isset($_GET['city']) || isset($_GET['city_id']) || isset($_GET['sort'])
            || isset($_GET['q']) || isset($_GET['price_range']) || isset($_GET['area_range']) || isset($_GET['rooms'])
            || isset($_GET['view']) || isset($_GET['types'])
        ) {
            $type  = isset($_GET['type']) ? trim($_GET['type']) : '';
            $stype = isset($_GET['stype']) ? trim($_GET['stype']) : '';
            $city  = isset($_GET['city']) ? trim($_GET['city']) : '';
            $cityId = isset($_GET['city_id']) ? (int) $_GET['city_id'] : 0;
            $sort  = isset($_GET['sort']) ? trim($_GET['sort']) : '';
            $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
            $priceRange = isset($_GET['price_range']) ? trim($_GET['price_range']) : '';
            $areaRange = isset($_GET['area_range']) ? trim($_GET['area_range']) : '';
            $rooms = isset($_GET['rooms']) ? trim($_GET['rooms']) : '';
            $viewMode = isset($_GET['view']) ? trim($_GET['view']) : 'grid';
            $selectedTypes = isset($_GET['types']) && is_array($_GET['types'])
                ? array_values(array_unique(array_filter(array_map('trim', $_GET['types']))))
                : [];
            if (!empty($selectedTypes)) {
                $type = '';
            }
            $isFiltering = (
                !empty($type) || !empty($stype) || !empty($cityId) || !empty($keyword)
                || !empty($priceRange) || !empty($areaRange) || !empty($rooms) || !empty($selectedTypes)
            );
        }

        if (!in_array($viewMode, ['grid', 'list'], true)) {
            $viewMode = 'grid';
        }

        if ($isFiltering) {
            $properties = $propertyModel->getPropertiesByFilter($type, $stype, $cityId, [
                'keyword' => $keyword,
                'price_range' => $priceRange,
                'area_range' => $areaRange,
                'rooms' => $rooms,
                'types' => $selectedTypes,
            ]);
        } else {
            $properties = $propertyModel->getAllProperties();
        }

        if (!empty($sort) && is_array($properties) && count($properties) > 1) {
            usort($properties, function ($a, $b) use ($sort) {
                switch ($sort) {
                    case 'verified_first':
                        $aVerified = (($a['approval_status'] ?? '') === 'approved') ? 1 : 0;
                        $bVerified = (($b['approval_status'] ?? '') === 'approved') ? 1 : 0;
                        return $bVerified <=> $aVerified;
                    case 'price_asc':
                        return (float)($a['price'] ?? 0) <=> (float)($b['price'] ?? 0);
                    case 'price_desc':
                        return (float)($b['price'] ?? 0) <=> (float)($a['price'] ?? 0);
                    case 'area_asc':
                        return (float)($a['size'] ?? 0) <=> (float)($b['size'] ?? 0);
                    case 'area_desc':
                        return (float)($b['size'] ?? 0) <=> (float)($a['size'] ?? 0);
                    default:
                        return 0;
                }
            });
        }

        $favoritePropertyIds = [];
        if (isset($_SESSION['uid'])) {
            $favoritePropertyIds = $propertyModel->getFavoritePropertyIds($_SESSION['uid']);
        }

        $data = [
            'properties' => $properties,
            'featuredProperties' => $propertyModel->getFeaturedProperties(3),
            'recentProperties' => $propertyModel->getRecentPropertiesLimit(6),
            'propertyTypes' => $propertyModel->getPropertyTypes(true),
            'cities' => $locationModel->getAllCities(),
            'filter_type' => $type,
            'filter_stype' => $stype,
            'filter_city' => $city,
            'filter_city_id' => $cityId,
            'sort' => $sort,
            'filter_q' => $keyword,
            'filter_price_range' => $priceRange,
            'filter_area_range' => $areaRange,
            'filter_rooms' => $rooms,
            'selected_types' => $selectedTypes,
            'view_mode' => $viewMode,
            'favoritePropertyIds' => $favoritePropertyIds,
        ];

        $this->view('property/index', $data);
    }

    public function detail($id = '') {
        if (empty($id)) {
            header("Location: " . BASEURL . "/property/index");
            exit;
        }

        $propertyModel = $this->model('Property');
        $property = $propertyModel->getPropertyById($id);

        if (empty($property)) {
            header("Location: " . BASEURL . "/property/index");
            exit;
        }

        $isOwner = isset($_SESSION['uid']) && (int) $_SESSION['uid'] === (int) $property['uid'];
        if (($property['approval_status'] ?? 'approved') !== 'approved' && !$isOwner) {
            header("Location: " . BASEURL . "/property/index");
            exit;
        }

        // Tăng view count với cookie check để tránh spam
        $cookieName = 'property_viewed_' . $id;
        if (!isset($_COOKIE[$cookieName])) {
            $propertyModel->incrementViewCount($id);
            // Set cookie hết hạn sau 24 giờ
            setcookie($cookieName, '1', time() + 86400, '/');
        }

        $data = [
            'property' => $property,
            'featuredProperties' => $propertyModel->getFeaturedProperties(3),
            'recentProperties' => $propertyModel->getRecentPropertiesLimit(7),
            'isFavorited' => isset($_SESSION['uid']) ? $propertyModel->isPropertyFavorited($_SESSION['uid'], $property['pid']) : false,
            'agentPropertyCount' => !empty($property['uid']) ? count($propertyModel->getApprovedPropertiesByUser((int)$property['uid'])) : 0,
        ];

        $this->view('property/detail', $data);
    }

    public function favorites() {
        if (!isset($_SESSION['uemail'])) {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        $propertyModel = $this->model('Property');
        $favorites = $propertyModel->getFavoriteProperties($_SESSION['uid']);

        $data = [
            'properties' => $favorites,
            'recentProperties' => $propertyModel->getRecentPropertiesLimit(6),
            'favoritePropertyIds' => $propertyModel->getFavoritePropertyIds($_SESSION['uid']),
            'pageTitle' => 'Tin đăng yêu thích',
            'pageHeadline' => 'Danh sách tin đăng yêu thích',
            'breadcrumbLabel' => 'Yêu thích',
            'totalLabel' => 'Hiện có',
            'emptyMessage' => 'Bạn chưa lưu bài đăng nào.'
        ];

        $this->view('property/favorites', $data);
    }

    public function toggleFavorite($id = '') {
        if (!isset($_SESSION['uemail'])) {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        if (empty($id)) {
            header("Location: " . BASEURL . "/property/index");
            exit;
        }

        $propertyModel = $this->model('Property');
        $property = $propertyModel->getPublicPropertyById($id);

        if (empty($property)) {
            header("Location: " . BASEURL . "/property/index");
            exit;
        }

        $propertyModel->toggleFavorite($_SESSION['uid'], $id);

        $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : '';
        if ($redirect === '') {
            $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASEURL . '/property/index';
        }

        if (strpos($redirect, BASEURL) !== 0) {
            $redirect = BASEURL . '/property/index';
        }

        header('Location: ' . $redirect);
        exit;
    }

    public function create() {
        if (!isset($_SESSION['uemail'])) {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        if (!$this->canCurrentUserPostProperty()) {
            $this->redirectPostingNotAllowed();
        }

        $data = [
            'msg' => '',
            'error' => ''
        ];

        $locationModel = $this->model('Location');
        $data['cities'] = $locationModel->getAllCities();
        $data['wards'] = $locationModel->getAllWards();
        $data['propertyTypes'] = $this->model('Property')->getPropertyTypes(true);

        if (isset($_POST['add'])) {
            $postData = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'ptype' => $_POST['ptype'],
                'type_id' => isset($_POST['type_id']) ? (int) $_POST['type_id'] : 0,
                'direction' => $_POST['direction'],
                'stype' => $_POST['stype'],
                'bed' => $_POST['bed'],
                'bath' => $_POST['bath'],
                'balc' => $_POST['balc'],
                'kitc' => $_POST['kitc'],
                'hall' => $_POST['hall'],
                'floor' => $_POST['floor'],
                'price' => $_POST['price'],
                'city' => '',
                'city_id' => isset($_POST['city_id']) ? (int) $_POST['city_id'] : 0,
                'asize' => $_POST['asize'],
                'loc' => $_POST['loc'],
                'ward_id' => isset($_POST['ward_id']) ? (int) $_POST['ward_id'] : 0,
                'status' => $_POST['status'],
                'uid' => $_SESSION['uid'],
                'property_age' => $_POST['property_age'] ?? '',
                'swimming_pool' => $_POST['swimming_pool'] ?? 0,
                'parking' => $_POST['parking'] ?? 0,
                'gym' => $_POST['gym'] ?? 0,
                'near_school' => $_POST['near_school'] ?? 0,
                'security' => $_POST['security'] ?? 0,
                'near_hospital' => $_POST['near_hospital'] ?? 0,
                'near_market' => $_POST['near_market'] ?? 0,
                'wifi' => $_POST['wifi'] ?? 0,
                'elevator' => $_POST['elevator'] ?? 0,
                'cctv' => $_POST['cctv'] ?? 0,
                'water_source' => $_POST['water_source'] ?? '',
                'frontage_m' => $_POST['frontage_m'] ?? '',
                'access_road_m' => $_POST['access_road_m'] ?? '',
                'interior_level' => $_POST['interior_level'] ?? '',
                'totalfl' => $_POST['totalfl'],
                'isFeatured' => $_POST['isFeatured']
            ];

            $uploadDir = "../admin/property/";
            $images = [];
            $filesToMove = [
                'aimage' => 'aimage', 'aimage1' => 'aimage1', 'aimage2' => 'aimage2',
                'aimage3' => 'aimage3', 'aimage4' => 'aimage4',
                'fimage' => 'fimage', 'fimage1' => 'fimage1', 'fimage2' => 'fimage2'
            ];

            foreach ($filesToMove as $dbField => $inputName) {
                if (isset($_FILES[$inputName]['name']) && !empty($_FILES[$inputName]['name'])) {
                    $fileName = $_FILES[$inputName]['name'];
                    $tempName = $_FILES[$inputName]['tmp_name'];
                    move_uploaded_file($tempName, $uploadDir . $fileName);
                    $images[$dbField] = $fileName;
                } else {
                    $images[$dbField] = '';
                }
            }

            $propertyModel = $this->model('Property');
            if ($propertyModel->addProperty($postData, $images)) {
                $data['msg'] = "<p class='alert alert-success'>�ang tin th�nh c�ng. B�i dang dang ch? qu?n tr? vi�n ph� duy?t.</p>";
            } else {
                $data['error'] = "<p class='alert alert-warning'>Kh�ng th? dang b?t d?ng s?n, c� l?i x?y ra</p>";
            }
        }

        $this->view('property/create', $data);
    }

    public function feature() {
        if (!isset($_SESSION['uemail'])) {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        if (!$this->canCurrentUserPostProperty()) {
            $this->redirectPostingNotAllowed();
        }

        $propertyModel = $this->model('Property');
        $propertyModel->markApprovalNotificationsSeen($_SESSION['uid']);
        $currentUserType = $this->getCurrentUserType();

        $data = [
            'properties' => $propertyModel->getPropertiesByUser($_SESSION['uid']),
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
            'userType' => $currentUserType,
            'inquiries' => in_array($currentUserType, ['owner', 'agent'], true)
                ? $propertyModel->getInquiriesByAgent($_SESSION['uid'])
                : []
        ];

        $this->view('property/feature', $data);
    }

    public function delete($id = '') {
        if (!isset($_SESSION['uemail']) || empty($id)) {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        if (!$this->canCurrentUserPostProperty()) {
            $this->redirectPostingNotAllowed();
        }

        $propertyModel = $this->model('Property');
        if ($propertyModel->deleteProperty($id, $_SESSION['uid'])) {
            $msg = "<p class='alert alert-success'>�� x�a b?t d?ng s?n v� d?n d?p c�c t?p ?nh li�n quan</p>";
        } else {
            $msg = "<p class='alert alert-danger'>L?i: Kh�ng t�m th?y b?t d?ng s?n ho?c b?n kh�ng c� quy?n x�a!</p>";
        }

        header("Location: " . BASEURL . "/property/feature?msg=" . urlencode($msg));
        exit;
    }

    public function update($id = '') {
        if (!isset($_SESSION['uemail']) || empty($id)) {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        if (!$this->canCurrentUserPostProperty()) {
            $this->redirectPostingNotAllowed();
        }

        $propertyModel = $this->model('Property');
        $property = $propertyModel->getPropertyById($id);

        if (empty($property) || $property['uid'] != $_SESSION['uid']) {
            $msg = "<p class='alert alert-danger'>L?i b?o m?t: B?n kh�ng c� quy?n truy c?p ho?c s?a t�i s?n n�y!</p>";
            header("Location: " . BASEURL . "/property/feature?msg=" . urlencode($msg));
            exit;
        }

        $data = [
            'property' => $property,
            'msg' => '',
            'error' => ''
        ];

        $locationModel = $this->model('Location');
        $data['cities'] = $locationModel->getAllCities();
        $data['wards'] = $locationModel->getAllWards();
        $data['propertyTypes'] = $propertyModel->getPropertyTypes(true);

        if (isset($_POST['add'])) {
            $postData = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'ptype' => $_POST['ptype'],
                'type_id' => isset($_POST['type_id']) ? (int) $_POST['type_id'] : 0,
                'direction' => $_POST['direction'],
                'stype' => $_POST['stype'],
                'bed' => $_POST['bed'],
                'bath' => $_POST['bath'],
                'balc' => $_POST['balc'],
                'kitc' => $_POST['kitc'],
                'hall' => $_POST['hall'],
                'floor' => $_POST['floor'],
                'price' => $_POST['price'],
                'city' => '',
                'city_id' => isset($_POST['city_id']) ? (int) $_POST['city_id'] : 0,
                'asize' => $_POST['asize'],
                'loc' => $_POST['loc'],
                'ward_id' => isset($_POST['ward_id']) ? (int) $_POST['ward_id'] : 0,
                'status' => $_POST['status'],
                'property_age' => $_POST['property_age'] ?? '',
                'swimming_pool' => $_POST['swimming_pool'] ?? 0,
                'parking' => $_POST['parking'] ?? 0,
                'gym' => $_POST['gym'] ?? 0,
                'near_school' => $_POST['near_school'] ?? 0,
                'security' => $_POST['security'] ?? 0,
                'near_hospital' => $_POST['near_hospital'] ?? 0,
                'near_market' => $_POST['near_market'] ?? 0,
                'wifi' => $_POST['wifi'] ?? 0,
                'elevator' => $_POST['elevator'] ?? 0,
                'cctv' => $_POST['cctv'] ?? 0,
                'water_source' => $_POST['water_source'] ?? '',
                'frontage_m' => $_POST['frontage_m'] ?? '',
                'access_road_m' => $_POST['access_road_m'] ?? '',
                'interior_level' => $_POST['interior_level'] ?? '',
                'totalfl' => $_POST['totalfl'],
                'isFeatured' => $property['isFeatured']
            ];

            $uploadDir = "../admin/property/";
            $images = [];
            $filesToMove = [
                'aimage' => 'aimage', 'aimage1' => 'aimage1', 'aimage2' => 'aimage2',
                'aimage3' => 'aimage3', 'aimage4' => 'aimage4',
                'fimage' => 'fimage', 'fimage1' => 'fimage1', 'fimage2' => 'fimage2'
            ];

            foreach ($filesToMove as $dbField => $inputName) {
                if (isset($_FILES[$inputName]['name']) && !empty($_FILES[$inputName]['name'])) {
                    $fileName = $_FILES[$inputName]['name'];
                    $tempName = $_FILES[$inputName]['tmp_name'];

                    $oldImage = $property[$dbField];
                    if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) {
                        unlink($uploadDir . $oldImage);
                    }

                    move_uploaded_file($tempName, $uploadDir . $fileName);
                    $images[$dbField] = $fileName;
                } else {
                    $images[$dbField] = '';
                }
            }

            if ($propertyModel->updateProperty($id, $_SESSION['uid'], $postData, $images)) {
                $msg = "<p class='alert alert-success'>C?p nh?t th�nh c�ng. B�i dang d� du?c g?i l?i d? ch? ph� duy?t.</p>";
                header("Location: " . BASEURL . "/property/feature?msg=" . urlencode($msg));
                exit;
            } else {
                $data['error'] = "<p class='alert alert-warning'>Kh�ng th? c?p nh?t b?t d?ng s?n</p>";
            }
        }

        $this->view('property/update', $data);
    }

    public function submitInquiry($id = '') {
        if (!isset($_POST['send_inquiry']) || empty($id)) {
            header('Location: ' . BASEURL . '/property/detail/' . (int) $id);
            exit;
        }

        $propertyModel = $this->model('Property');
        $property = $propertyModel->getPublicPropertyById((int) $id);

        if (!$property) {
            header('Location: ' . BASEURL . '/property/index');
            exit;
        }

        $propertyStatus = strtolower((string) ($property['status'] ?? 'available'));
        if ($propertyStatus === 'rented') {
            $msg = "<p class='alert alert-danger'>BĐS này đã được thuê. Không thể gửi yêu cầu.</p>";
            header('Location: ' . BASEURL . '/property/detail/' . (int) $id . '?msg=' . urlencode($msg));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['work_email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $requirement = trim($_POST['requirement'] ?? '');
        $desiredBudget = trim($_POST['desired_budget'] ?? '');
        $desiredArea = trim($_POST['desired_area'] ?? '');
        $desiredMoveInTime = trim($_POST['desired_move_in_time'] ?? '');

        if ($name === '' || $email === '' || $phone === '' || $requirement === '') {
            $msg = "<p class='alert alert-warning'>Vui lòng nhập đầy đủ thông tin liên hệ.</p>";
            header('Location: ' . BASEURL . '/property/detail/' . (int) $id . '?msg=' . urlencode($msg));
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = "<p class='alert alert-warning'>Email công việc không hợp lệ.</p>";
            header('Location: ' . BASEURL . '/property/detail/' . (int) $id . '?msg=' . urlencode($msg));
            exit;
        }

        $saved = $propertyModel->addPropertyInquiry([
            'property_id' => (int) $property['pid'],
            'agent_uid' => (int) $property['uid'],
            'inquirer_uid' => isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null,
            'name' => $name,
            'work_email' => $email,
            'phone' => $phone,
            'requirement' => $requirement,
            'desired_budget' => $desiredBudget,
            'desired_area' => $desiredArea,
            'desired_move_in_time' => $desiredMoveInTime,
        ]);

        if ($saved) {
            $msg = "<p class='alert alert-success'>Đã gửi yêu cầu liên hệ thành công. Người đăng sẽ phản hồi sớm.</p>";
        } else {
            $msg = "<p class='alert alert-danger'>Không thể gửi yêu cầu liên hệ. Vui lòng thử lại.</p>";
        }

        header('Location: ' . BASEURL . '/property/detail/' . (int) $id . '?msg=' . urlencode($msg));
        exit;
    }

    public function ownerCall($id = '') {
        $id = (int) $id;
        if ($id <= 0) {
            header('Location: ' . BASEURL . '/property/index');
            exit;
        }

        $propertyModel = $this->model('Property');
        $property = $propertyModel->getPublicPropertyById($id);
        if (empty($property)) {
            header('Location: ' . BASEURL . '/property/index');
            exit;
        }

        $ownerType = strtolower((string) ($property['utype'] ?? ''));
        $isOwnerDirect = in_array($ownerType, ['owner', 'user', 'renter'], true);
        $phone = trim((string) ($property['uphone'] ?? ''));
        $detailUrl = BASEURL . '/property/detail/' . $id;

        if ($isOwnerDirect) {
            $propertyModel->trackOwnerCallClick(
                (int) $property['pid'],
                (int) $property['uid'],
                isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null,
                isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null,
                isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : null
            );
        }

        if ($phone === '') {
            $msg = "<p class='alert alert-warning'>Tin đăng chưa cập nhật số điện thoại liên hệ.</p>";
            header('Location: ' . $detailUrl . '?msg=' . urlencode($msg));
            exit;
        }

        $safePhone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $safeDetailUrl = htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8');

        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Đang chuyển cuộc gọi...</title></head><body>';
        echo '<p>Đang mở ứng dụng gọi điện...</p>';
        echo '<script>';
        echo 'window.location.href = "tel:' . $safePhone . '";';
        echo 'setTimeout(function(){ window.location.href = "' . $safeDetailUrl . '"; }, 1200);';
        echo '</script>';
        echo '</body></html>';
        exit;
    }
}
