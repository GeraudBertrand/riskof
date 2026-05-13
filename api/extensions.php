<?php
require_once __DIR__ . '/Model/db_connect.php';
$model = Model::getModel();

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Par défaut : seulement les DLC (icon NOT NULL).
        // ?all=1 pour récupérer Vanilla incluse aussi.
        $withVanilla = !empty($_GET['all']);
        echo json_encode($model->get_extensions($withVanilla), JSON_PRETTY_PRINT);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
