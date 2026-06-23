<?php
require_once '../includes/config.php';

if (isset($_SESSION['admin_logado'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <div class="logo"><i class="fas fa-user-cog"></i> Painel Administrativo</div>
        <nav>
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Voltar</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>
    <center><img src="../assets/img/nfse.png" alt="Sistema NFS-e" width="320" height="200"></center>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-lock"></i> Acesso Restrito</h2>
            <?php if (isset($_SESSION['erro_admin'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['erro_admin']; unset($_SESSION['erro_admin']); ?></div>
            <?php endif; ?>
            <form action="auth.php" method="POST">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" name="email" required placeholder="admin@seudominio.com">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Senha</label>
                    <input type="password" name="password" required>
                </div>
                <?php if (TURNSTILE_ENABLED): ?>
                    <div class="form-group">
                        <div class="cf-turnstile" data-sitekey="<?= TURNSTILE_SITE_KEY ?>" data-theme="<?= TURNSTILE_THEME ?>"></div>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-block">Entrar</button>
            </form>
        </div>
    </div>
    <footer>
        <i class="fas fa-user-cog"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>
    <script src="../assets/js/script.js"></script>
</body>
</html>
