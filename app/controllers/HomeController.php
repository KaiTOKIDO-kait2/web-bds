<?php
class HomeController extends Controller {
    public function index() {
        $propertyModel = $this->model('Property');
        $locationModel = $this->model('Location');
        $userModel = $this->model('User');
        $favoritePropertyIds = [];

        if (isset($_SESSION['uid'])) {
            $favoritePropertyIds = $propertyModel->getFavoritePropertyIds($_SESSION['uid']);
        }
        
        $data = [
            'recentProperties' => $propertyModel->getRecentPropertiesLimit(9),
            'favoritePropertyIds' => $favoritePropertyIds,
            'cities'           => $locationModel->getAllCities(),
            'totalProperties'  => $propertyModel->countProperties(),
            'saleProperties'   => $propertyModel->countSaleProperties(),
            'rentProperties'   => $propertyModel->countRentProperties(),
            'totalUsers'       => $userModel->countUsers(),
            'agents'           => $userModel->getAgents(),
            'cityOlisphis'     => $propertyModel->countPropertiesByCity('Olisphis'),
            'cityAwrerton'     => $propertyModel->countPropertiesByCity('Awrerton'),
            'cityFloson'       => $propertyModel->countPropertiesByCity('Floson'),
            'cityUlmore'       => $propertyModel->countPropertiesByCity('Ulmore'),
            'feedbacks'        => $this->model('Feedback')->getApprovedFeedback()
        ];

        $this->view('home/index', $data);
    }
}
