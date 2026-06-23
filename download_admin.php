<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verifica se é admin
if (!is_admin()) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['user_id']) || !isset($_GET['file'])) {
    die('Parâmetros inválidos.');
}

$user_id = (int)$_GET['user_id'];
$arquivo_relativo = $_GET['file'];

// Busca a pasta do usuário
$stmt = $pdo->prepare("SELECT pasta FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();
if (!$usuario) {
    die('Usuário não encontrado.');
}

$caminho_base = UPLOAD_DIR . $usuario['pasta'];
$arquivo_real = realpath($caminho_base . DIRECTORY_SEPARATOR . $arquivo_relativo);

// Verifica se o arquivo está dentro da pasta do usuário
if (strpos($arquivo_real, realpath($caminho_base)) !== 0) {
    die('Acesso negado.');
}

if (!file_exists($arquivo_real) || is_dir($arquivo_real)) {
    die('Arquivo não encontrado ou é um diretório.');
}

// Força download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($arquivo_real) . '"');
header('Content-Length: ' . filesize($arquivo_real));
readfile($arquivo_real);
exit;
?>
