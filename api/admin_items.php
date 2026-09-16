<?php

declare(strict_types=1);

require_once __DIR__ . '/Model/db_connect.php';
$credentials = require __DIR__ . '/../admin/auth_credentials.php';

function respond(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_admin(array $credentials): void {
    if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
        || !hash_equals((string)$credentials['user'], (string)$_SERVER['PHP_AUTH_USER'])
        || !hash_equals((string)$credentials['pass'], (string)$_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Risk of Library admin"');
        respond(['error' => 'Authentication required'], 401);
    }
}

function nullable_number(string $value): ?float {
    return $value === '' ? null : (float)$value;
}

function item_data(array $source, string $image): array {
    $required = ['name', 'rarity', 'orig', 'description', 'effect'];
    foreach ($required as $field) {
        if (!isset($source[$field]) || trim((string)$source[$field]) === '') {
            respond(['error' => 'Missing field: ' . $field], 422);
        }
    }

    $rarity = (int)$source['rarity'];
    $orig = (int)$source['orig'];
    if ($rarity < 1 || $rarity > 10 || $orig < 1 || $orig > 4) {
        respond(['error' => 'Rarity or origin is outside the supported range'], 422);
    }

    return [
        'name' => trim((string)$source['name']),
        'rarity' => $rarity,
        'orig' => $orig,
        'description' => trim((string)$source['description']),
        'image' => $image,
        'effect' => trim((string)$source['effect']),
        'base1' => nullable_number((string)($source['base1'] ?? '')),
        'stack1' => nullable_number((string)($source['stack1'] ?? '')),
        'base2' => nullable_number((string)($source['base2'] ?? '')),
        'stack2' => nullable_number((string)($source['stack2'] ?? '')),
        'base3' => nullable_number((string)($source['base3'] ?? '')),
        'stack3' => nullable_number((string)($source['stack3'] ?? '')),
    ];
}

function upload_image(?array $upload): ?string {
    if (!$upload || $upload['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 5 * 1024 * 1024) {
        respond(['error' => 'Image upload failed or exceeds 5 MB'], 422);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    $extensions = ['image/webp' => 'webp', 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif'];
    if (!isset($extensions[$mime])) {
        respond(['error' => 'Unsupported image format'], 422);
    }

    $original = pathinfo((string)$upload['name'], PATHINFO_FILENAME);
    $filename = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $original);
    $filename = trim((string)$filename, '_');
    if ($filename === '') {
        respond(['error' => 'Image filename is invalid'], 422);
    }
    $filename .= '.' . $extensions[$mime];

    $directory = __DIR__ . '/../Content/image/item/';
    if (!move_uploaded_file($upload['tmp_name'], $directory . $filename)) {
        respond(['error' => 'Unable to store image'], 500);
    }
    return $filename;
}

require_admin($credentials);
$model = Model::getModel();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        echo json_encode($model->get_items() ?? [], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $uploadedImage = upload_image($_FILES['image'] ?? null);
        $image = $uploadedImage ?: trim((string)($_POST['image_name'] ?? ''));
        if ($image === '') {
            respond(['error' => 'An image filename or upload is required'], 422);
        }

        $data = item_data($_POST, $image);
        if ($id === null) {
            $newId = $model->create_item($data);
            respond(['id' => $newId, 'message' => 'Item created'], 201);
        }
        $model->update_item($id, $data);
        respond(['id' => $id, 'message' => 'Item updated']);
    }

    if ($method === 'DELETE') {
        parse_str(file_get_contents('php://input'), $body);
        $id = (int)($body['id'] ?? 0);
        if ($id < 1 || !$model->delete_item($id)) {
            respond(['error' => 'Item not found'], 404);
        }
        respond(['message' => 'Item deleted']);
    }

    respond(['error' => 'Method not allowed'], 405);
} catch (Throwable $error) {
    respond(['error' => 'Server error'], 500);
}
