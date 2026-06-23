<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
if (!isset($_SESSION['cpf_tmp'])) {
    header('Location: index.php');
    exit;
}

$cpf_cnpj = $_SESSION['cpf_tmp'];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo'])) {
    $codigo_digitado = trim($_POST['codigo']);
    $stmt = $pdo->prepare("SELECT id, nome, pasta FROM usuarios WHERE cpf_cnpj = ? AND codigo_verificacao = ?");
    $stmt->execute([$cpf_cnpj, $codigo_digitado]);
    $usuario = $stmt->fetch();

    if ($usuario && codigo_valido($pdo, $cpf_cnpj, $codigo_digitado)) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_pasta'] = $usuario['pasta'];
        $stmt = $pdo->prepare("UPDATE usuarios SET codigo_verificacao = NULL, codigo_expiracao = NULL WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        unset($_SESSION['cpf_tmp']);
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = 'Código inválido ou expirado. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Verificar Código</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-file-invoice"></i> NFS-e
            <small>Nota Fiscal de Serviços</small>
        </div>
        <nav>
            <a href="index.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-shield-alt"></i> Autenticação 2FA</h2>
            <p style="text-align:center; color:#aaa; margin-bottom:20px;">
                Informe o código que enviamos para o e-mail cadastrado.
            </p>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $erro ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="codigo"><i class="fas fa-key"></i> Código de Verificação</label>
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
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
