<?php
require_once __DIR__ . '/Model/db_connect.php';
$model = Model::getModel();

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!empty($_GET['id'])) {
            $data = $model->get_equipment((int)$_GET['id']);
        } else {
            $data = $model->get_equipments();
        }
        echo json_encode($data, JSON_PRETTY_PRINT);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
