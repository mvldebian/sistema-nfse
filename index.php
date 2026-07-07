<?php
require_once 'includes/config.php';
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php if (TURNSTILE_ENABLED): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad" async defer></script>
    <?php endif; ?>
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
        var TURNSTILE_ENABLED = <?php echo json_encode(TURNSTILE_ENABLED); ?>;
        var TURNSTILE_THEME = <?php echo json_encode(TURNSTILE_THEME); ?>;
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-file-invoice"></i> NFS-e
            <small>Nota Fiscal de Serviços</small>
        </div>
        <nav>
            <a href="/admin/index.php"><i class="fas fa-sign-in-alt"></i> Painel Administrativo</a>
            <a href="contador_login.php"><i class="fas fa-sign-in-alt"></i> Acesso do Contador</a>
            <a href="index.php"><i class="fas fa-user-tie"></i> Página Inicial</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>
    <center><img src="assets/img/nfse.png" alt="Sistema NFS-e" width="320" height="200"></center>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-lock"></i> Acesse sua Conta</h2>
            <?php if (isset($_SESSION['erro_login'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['erro_login']; unset($_SESSION['erro_login']); ?>
                </div>
            <?php endif; ?>
            <form action="auth.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="cpf_cnpj"><i class="fas fa-id-card"></i> CPF ou CNPJ</label>
                    <input type="text" name="cpf_cnpj" id="cpf_cnpj" placeholder="Digite apenas números" required>
                </div>
                <?php if (TURNSTILE_ENABLED): ?>
                    <div class="form-group">
                        <div class="cf-turnstile" data-sitekey="<?= TURNSTILE_SITE_KEY ?>" data-theme="<?= TURNSTILE_THEME ?>"></div>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-block"><i class="fas fa-paper-plane"></i> Enviar código</button>
            </form>
            <p style="text-align:center; margin-top:20px; font-size:0.9rem; color:#888;">
                <i class="fas fa-info-circle"></i> Você receberá um código em seu e-mail de Cadastro.
            </p>
        </div>

        <div class="infobox">
            <h3><i class="fas fa-shield-alt"></i> Segurança e Conformidade</h3><br>
            <p>
                O <strong>Sistema NFS-e</strong> é uma solução confiável para guarda de notas fiscais de serviços eletrônicas.<br>
                Armazenamos tudo com criptografia e backup, garantindo a integridade e disponibilidade dos dados.<br><br>
            </p>
            <ul>
                <li><i class="fas fa-check-circle"></i> Atende à <strong>LGPD</strong></li>
                <li><i class="fas fa-check-circle"></i> Backup Automático</li>
                <li><i class="fas fa-check-circle"></i> Autenticação 2FA</li>
                <li><i class="fas fa-check-circle"></i> Auditoria Completa</li>
            </ul>
        </div>
    </div>

    <footer>
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
