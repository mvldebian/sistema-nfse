<?php
session_start();
require_once '../includes/config.php';

// Se já estiver logado, vai para o dashboard
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Se não houver código na sessão (tentativa direta), volta para login
if (!isset($_SESSION['admin_codigo'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo'])) {
    $codigo_digitado = trim($_POST['codigo']);
    $codigo_salvo = $_SESSION['admin_codigo'];
    $expiracao_salva = $_SESSION['admin_codigo_expiracao'];

    // Verifica se o código expirou
    $agora = new DateTime();
    $expiracao = new DateTime($expiracao_salva);

    if ($codigo_digitado === $codigo_salvo && $agora <= $expiracao) {
        // Código válido! Autentica o admin
        $_SESSION['admin_logado'] = true;
        // Limpa os dados temporários
        unset($_SESSION['admin_codigo']);
        unset($_SESSION['admin_codigo_expiracao']);
        header('Location: dashboard.php');
        exit;
    } else {
        if ($codigo_digitado !== $codigo_salvo) {
            $erro = 'Código inválido. Tente novamente.';
        } else {
            $erro = 'Código expirado. Solicite um novo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Verificar código</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-user-cog"></i> Admin
        </div>
        <nav>
            <a href="index.php"><i class="fas fa-arrow-left"></i> Voltar</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-shield-alt"></i> Verificação em duas etapas</h2>
            <p style="text-align:center; color:#555; margin-bottom:20px;">
                Enviamos um código de 6 dígitos para o e-mail do administrador.<br>
                Digite-o abaixo para concluir o login.
            </p>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $erro ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="codigo"><i class="fas fa-key"></i> Código de verificação</label>
                    <input type="text" name="codigo" id="codigo" placeholder="Ex: 123456" required maxlength="6" autofocus>
                </div>
                <button type="submit" class="btn btn-block"><i class="fas fa-check-circle"></i> Verificar</button>
            </form>
            <p style="text-align:center; margin-top:20px; font-size:0.9rem; color:#888;">
                <i class="fas fa-clock"></i> O código expira em 5 minutos.
            </p>
        </div>
    </div>

    <footer>
        <i class="fas fa-user-cog"></i> Painel Administrativo &bull; &copy; <?= date('Y') ?>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
