<?php
session_start();
if (!isset($_SESSION['admin_logado'])) {
    http_response_code(403);
    die('Acesso negado.');
}

$upload_dir = __DIR__ . '/../anexos/temp/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(['error' => 'Formato de arquivo não permitido.']);
        exit;
    }
    
    $filename = uniqid() . '.' . $ext;
    $destination = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $url = '../anexos/temp/' . $filename;
        echo json_encode(['location' => $url]);
    } else {
        echo json_encode(['error' => 'Erro ao fazer upload.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
}
?>
