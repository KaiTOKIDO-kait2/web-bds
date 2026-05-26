<?php
class AdminPropertyTypeController extends Controller
{
    private $propertyTypeModel;

    public function __construct()
    {
        if (!isset($_SESSION['auser'])) {
            header("Location: " . BASEURL . "/admin/index");
            exit();
        }

        $this->propertyTypeModel = $this->model('PropertyType');
    }

    private function redirectIndex($message)
    {
        header("Location: " . BASEURL . "/adminPropertyType/index?msg=" . urlencode($message));
        exit();
    }

    public function index()
    {
        $data = [
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
            'error' => '',
            'types' => $this->propertyTypeModel->getAll(true)
        ];

        if (isset($_POST['insert'])) {
            $name = $_POST['name'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $result = $this->propertyTypeModel->create($name, $slug, $sortOrder, $isActive);
            if (!empty($result['ok'])) {
                $this->redirectIndex("<p class='alert alert-success'>" . $result['message'] . "</p>");
            }
            $data['error'] = "<p class='alert alert-warning'>" . ($result['message'] ?? 'Không thể thêm loại bất động sản.') . "</p>";
            $data['types'] = $this->propertyTypeModel->getAll(true);
        }

        $this->view('admin/property_type/index', $data);
    }

    public function edit($id = 0)
    {
        $id = (int) $id;
        $type = $this->propertyTypeModel->getById($id);
        if (empty($type)) {
            $this->redirectIndex("<p class='alert alert-warning'>Không tìm thấy loại bất động sản.</p>");
        }

        $data = [
            'msg' => isset($_GET['msg']) ? urldecode($_GET['msg']) : '',
            'error' => '',
            'type' => $type,
        ];

        if (isset($_POST['update'])) {
            $name = $_POST['name'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $result = $this->propertyTypeModel->update($id, $name, $slug, $sortOrder, $isActive);
            if (!empty($result['ok'])) {
                $this->redirectIndex("<p class='alert alert-success'>" . $result['message'] . "</p>");
            }

            $data['error'] = "<p class='alert alert-warning'>" . ($result['message'] ?? 'Không thể cập nhật loại bất động sản.') . "</p>";
            $data['type'] = $this->propertyTypeModel->getById($id);
        }

        $this->view('admin/property_type/edit', $data);
    }

    public function toggle($id = 0)
    {
        $id = (int) $id;
        $result = $this->propertyTypeModel->toggleActive($id);
        if (!empty($result['ok'])) {
            $this->redirectIndex("<p class='alert alert-success'>" . $result['message'] . "</p>");
        }

        $this->redirectIndex("<p class='alert alert-warning'>" . ($result['message'] ?? 'Không thể cập nhật trạng thái.') . "</p>");
    }

    public function delete($id = 0)
    {
        $id = (int) $id;
        $result = $this->propertyTypeModel->delete($id);
        if (!empty($result['ok'])) {
            $this->redirectIndex("<p class='alert alert-success'>" . $result['message'] . "</p>");
        }

        $this->redirectIndex("<p class='alert alert-warning'>" . ($result['message'] ?? 'Không thể xóa loại bất động sản.') . "</p>");
    }
}
