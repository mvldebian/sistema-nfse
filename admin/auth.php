<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}

// ===== VALIDA TURNSTILE =====
if (TURNSTILE_ENABLED) {
    $token = $_POST['cf-turnstile-response'] ?? '';
    if (!validar_turnstile($token)) {
        $_SESSION['erro_admin'] = 'Falha na verificação de segurança. Tente novamente.';
        header('Location: index.php');
        exit;
    }
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['password'] ?? '';

if (empty($email) || empty($senha)) {
    $_SESSION['erro_admin'] = 'Preencha todos os campos.';
    header('Location: index.php');
    exit;
}

// Busca usuário pelo e-mail e que seja admin
$stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ? AND is_admin = 1");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION['erro_admin'] = 'Credenciais inválidas.';
    header('Location: index.php');
    exit;
}

// Verifica a senha
if (!password_verify($senha, $admin['senha'])) {
    $_SESSION['erro_admin'] = 'Credenciais inválidas.';
    header('Location: index.php');
    exit;
}

// Login bem-sucedido
$_SESSION['admin_logado'] = true;
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_nome'] = $admin['nome'];
$_SESSION['admin_email'] = $admin['email'];
header('Location: dashboard.php');
exit;
?>
