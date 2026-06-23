<?php
require_once 'includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['file'])) {
    die('Arquivo não especificado.');
}

$pasta = $_SESSION['usuario_pasta'];
$caminho_base = UPLOAD_DIR . $pasta;
$arquivo_relativo = $_GET['file'];
$arquivo_real = realpath($caminho_base . DIRECTORY_SEPARATOR . $arquivo_relativo);

if (strpos($arquivo_real, realpath($caminho_base)) !== 0) {
    die('Acesso negado.');
}

if (!file_exists($arquivo_real) || is_dir($arquivo_real)) {
    die('Arquivo não encontrado ou é um diretório.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($arquivo_real) . '"');
header('Content-Length: ' . filesize($arquivo_real));
readfile($arquivo_real);
exit;
?>
