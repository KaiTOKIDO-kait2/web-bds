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
        $query = [];
        if (!empty($_GET['appointment_status'])) {
            $query['appointment_status'] = (string) $_GET['appointment_status'];
        }
        if (!empty($_GET['appointment_search'])) {
            $query['appointment_search'] = (string) $_GET['appointment_search'];
        }
        if (!empty($_GET['date_from'])) {
            $query['date_from'] = (string) $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $query['date_to'] = (string) $_GET['date_to'];
        }

        $target = BASEURL . '/adminInquiry/index';
        if (!empty($query)) {
            $target .= '?' . http_build_query($query);
        }
        $target .= '#appointments';

        header('Location: ' . $target);
        exit();
    }
}
