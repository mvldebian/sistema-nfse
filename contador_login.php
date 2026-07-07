<?php
require_once 'includes/config.php';
if (isset($_SESSION['contador_id'])) {
    header('Location: contador_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Acesso Contador</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-file-invoice"></i> NFS-e
        <small>Nota Fiscal de Serviços</small>
        </div>
        <nav>
            <a href="index.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>
    <center><img src="assets/img/nfse.png" alt="Sistema NFS-e" width="320" height="200"></center>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-user-tie"></i> Acesso para Contador</h2>
            <?php if (isset($_SESSION['erro_contador'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['erro_contador']; unset($_SESSION['erro_contador']); ?></div>
            <?php endif; ?>
            <form action="auth_contador.php" method="POST">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail do Contador</label>
                    <input type="email" name="email" id="email" placeholder="contador@contabilidadexyz.com.br" required>
                </div>
                <button type="submit" class="btn btn-block"><i class="fas fa-paper-plane"></i> Enviar código</button>
            </form>
            <p style="text-align:center; margin-top:20px;">
                <a href="index.php" style="color:#0d2b45;">Voltar ao login</a>
            </p>
        </div>
    </div>

    <footer>
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
