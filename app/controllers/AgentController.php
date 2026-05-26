<?php
class AgentController extends Controller {
    private function mapBrokerCards($brokers, $propertyModel, $categoryLabel) {
        $items = [];

        foreach ($brokers as $broker) {
            $recentProperties = $propertyModel->getApprovedPropertiesByUser($broker['uid'], 3);
            $areas = $propertyModel->getAgentAreaLabels($broker['uid'], 3);
            $primaryProperty = !empty($recentProperties) ? $recentProperties[0] : null;

            $items[] = [
                'broker' => $broker,
                'categoryLabel' => $categoryLabel,
                'recentProperties' => $recentProperties,
                'propertyCount' => count($propertyModel->getApprovedPropertiesByUser($broker['uid'])),
                'areaLabels' => $areas,
                'primaryAddress' => $primaryProperty['location'] ?? '',
                'primaryArea' => !empty($areas) ? implode(', ', $areas) : 'Chưa cập nhật khu vực môi giới'
            ];
        }

        return $items;
    }

    public function index() {
        $userModel = $this->model('User');
        $propertyModel = $this->model('Property');

        $brokerUsers = array_merge(
            $userModel->getUsersByType('agent'),
            $userModel->getUsersByType('owner')
        );
        $personalBrokers = $this->mapBrokerCards($brokerUsers, $propertyModel, 'Khu vực môi giới');
        $companyBrokers = [];
        
        $data = [
            'agents' => $personalBrokers,
            'companies' => $companyBrokers
        ];

        $this->view('agent/index', $data);
    }

    public function detail($id = '') {
        if (empty($id)) {
            header('Location: ' . BASEURL . '/agent/index');
            exit;
        }

        $userModel = $this->model('User');
        $propertyModel = $this->model('Property');
        $agent = $userModel->getUserById((int) $id);

        if (empty($agent) || !in_array($agent['utype'], ['owner', 'agent'], true)) {
            header('Location: ' . BASEURL . '/agent/index');
            exit;
        }

        $properties = $propertyModel->getApprovedPropertiesByUser((int) $id);
        $areaLabels = $propertyModel->getAgentAreaLabels((int) $id, 6);

        $data = [
            'agent' => $agent,
            'properties' => $properties,
            'areaLabels' => $areaLabels,
            'propertyCount' => count($properties),
            'categoryLabel' => 'Môi giới'
        ];

        $this->view('agent/detail', $data);
    }
}
