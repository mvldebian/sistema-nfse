<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

// ===== RECEBE PARÂMETROS =====
$item = isset($_GET['item']) ? trim($_GET['item']) : '';
$user_pasta = isset($_GET['user']) ? trim($_GET['user']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

error_log("DELETE: item=$item, user=$user_pasta, type=$type");

if (empty($item)) {
    $_SESSION['admin_erro'] = 'Parâmetro "item" não informado.';
    header('Location: browse.php' . ($user_pasta ? '?user=' . urlencode($user_pasta) : ''));
    exit;
}

// ===== CONSTRÓI O CAMINHO =====
// Se user_pasta estiver vazio, assumimos que o item está na raiz de uploads
if ($user_pasta) {
    $caminho_base = UPLOAD_DIR . $user_pasta;
} else {
    $caminho_base = UPLOAD_DIR;
}

$caminho_item = realpath($caminho_base . DIRECTORY_SEPARATOR . $item);

error_log("DELETE: caminho_base=$caminho_base, caminho_item=$caminho_item");

if ($caminho_item === false) {
    $_SESSION['admin_erro'] = 'Caminho inválido ou item não existe.';
    header('Location: browse.php' . ($user_pasta ? '?user=' . urlencode($user_pasta) : ''));
    exit;
}

$base_real = realpath(UPLOAD_DIR);
if (strpos($caminho_item, $base_real) !== 0) {
    $_SESSION['admin_erro'] = 'Acesso negado: item fora da pasta uploads.';
    header('Location: browse.php' . ($user_pasta ? '?user=' . urlencode($user_pasta) : ''));
    exit;
}

if (!file_exists($caminho_item)) {
    $_SESSION['admin_erro'] = 'Item não encontrado no servidor.';
    header('Location: browse.php' . ($user_pasta ? '?user=' . urlencode($user_pasta) : ''));
    exit;
}

// ===== FUNÇÃO PARA REMOVER PASTA =====
function remover_pasta($caminho) {
    if (!is_dir($caminho)) return false;
    $itens = scandir($caminho);
    foreach ($itens as $item) {
        if ($item === '.' || $item === '..') continue;
        $full_path = $caminho . DIRECTORY_SEPARATOR . $item;
        if (is_dir($full_path)) {
            if (!remover_pasta($full_path)) return false;
        } else {
            if (!unlink($full_path)) {
                error_log("Falha ao excluir arquivo: $full_path");
                return false;
            }
        }
    }
    return rmdir($caminho);
}

// ===== EXECUTA =====
$sucesso = false;
$erro_msg = '';

try {
    if ($type === 'dir') {
        $sucesso = remover_pasta($caminho_item);
        if (!$sucesso) {
            $erro_msg = 'Não foi possível remover a pasta (verifique permissões ou se está vazia).';
        }
    } elseif ($type === 'file') {
        if (is_file($caminho_item)) {
            $sucesso = unlink($caminho_item);
            if (!$sucesso) {
                $erro_msg = 'Não foi possível excluir o arquivo (permissões insuficientes).';
            }
        } else {
            $erro_msg = 'O item não é um arquivo válido.';
        }
    } else {
        $erro_msg = 'Tipo de item desconhecido.';
    }
} catch (Exception $e) {
    $erro_msg = 'Erro: ' . $e->getMessage();
    error_log("Erro ao excluir: " . $e->getMessage());
}

// ===== REDIRECIONA =====
if ($sucesso) {
    $_SESSION['admin_mensagem'] = 'Item excluído com sucesso.';
} else {
    $_SESSION['admin_erro'] = $erro_msg ?: 'Erro desconhecido ao excluir o item.';
}

$redirect_url = 'browse.php';
if ($user_pasta) {
    $redirect_url .= '?user=' . urlencode($user_pasta);
}
header('Location: ' . $redirect_url);
exit;
?>
