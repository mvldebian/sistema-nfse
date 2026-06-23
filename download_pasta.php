<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$pasta = $_SESSION['usuario_pasta'];
$caminho_base = UPLOAD_DIR . $pasta;

$diretorio_relativo = isset($_GET['dir']) ? trim($_GET['dir']) : '';
$caminho_pasta = realpath($caminho_base . DIRECTORY_SEPARATOR . $diretorio_relativo);

// Verifica se está dentro da pasta do usuário
if (strpos($caminho_pasta, realpath($caminho_base)) !== 0 || !is_dir($caminho_pasta)) {
    die('Acesso negado ou pasta inválida.');
}

// Nome do ZIP
$nome_zip = basename($caminho_pasta) . '.zip';
$zip_path = sys_get_temp_dir() . '/' . uniqid() . '.zip';

// Cria o ZIP
$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die('Erro ao criar arquivo ZIP.');
}

// Adiciona recursivamente
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($caminho_pasta),
    RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $caminho_relativo = substr($file->getPathname(), strlen($caminho_pasta) + 1);
        $zip->addFile($file->getPathname(), $caminho_relativo);
    }
}
$zip->close();

// Força download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $nome_zip . '"');
header('Content-Length: ' . filesize($zip_path));
readfile($zip_path);
unlink($zip_path);
exit;
?>
