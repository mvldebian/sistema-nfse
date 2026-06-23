<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['admin_erro'] = 'ID de usuário inválido.';
    header('Location: dashboard.php');
    exit;
}

// Busca o usuário e verifica se não é admin
$stmt = $pdo->prepare("SELECT id, nome, pasta FROM usuarios WHERE id = ? AND is_admin = 0");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $_SESSION['admin_erro'] = 'Usuário não encontrado ou é administrador.';
    header('Location: dashboard.php');
    exit;
}

// ===== EXCLUI A PASTA DO USUÁRIO =====
$caminho_pasta = UPLOAD_DIR . $usuario['pasta'];
$sucesso_pasta = true;

if (is_dir($caminho_pasta)) {
    // Função para remover recursivamente
    function remover_pasta($caminho) {
        if (!is_dir($caminho)) return false;
        $itens = scandir($caminho);
        foreach ($itens as $item) {
            if ($item === '.' || $item === '..') continue;
            $full_path = $caminho . DIRECTORY_SEPARATOR . $item;
            if (is_dir($full_path)) {
                remover_pasta($full_path);
            } else {
                unlink($full_path);
            }
        }
        return rmdir($caminho);
    }
    $sucesso_pasta = remover_pasta($caminho_pasta);
    if (!$sucesso_pasta) {
        error_log("Falha ao remover pasta do usuário: $caminho_pasta");
    }
}

// ===== EXCLUI DO BANCO =====
$stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
$sucesso_db = $stmt->execute([$id]);

if ($sucesso_db) {
    $_SESSION['admin_mensagem'] = 'Usuário excluído com sucesso!';
    if (!$sucesso_pasta) {
        $_SESSION['admin_mensagem'] .= ' (A pasta não pôde ser removida, verifique as permissões.)';
    }
} else {
    $_SESSION['admin_erro'] = 'Erro ao excluir o usuário do banco de dados.';
}

header('Location: dashboard.php');
exit;
?>
