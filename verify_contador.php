<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['contador_temp'])) {
    header('Location: contador_login.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo'])) {
    $codigo_digitado = trim($_POST['codigo']);
    $temp = $_SESSION['contador_temp'];

    if ($codigo_digitado == $temp['codigo'] && new DateTime() <= new DateTime($temp['expiracao'])) {
        // Código válido: cria sessão do contador
        $_SESSION['contador_id'] = $temp['id'];
        $_SESSION['contador_nome'] = $temp['nome'];
        $_SESSION['contador_usuario_id'] = $temp['usuario_id'];
        $_SESSION['contador_pasta'] = $temp['pasta'];
        $_SESSION['contador_usuario_nome'] = $temp['usuario_nome'];

        // Envia notificação para o usuário dono da pasta
        $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
        $stmt->execute([$temp['usuario_id']]);
        $usuario = $stmt->fetch();
        if ($usuario) {
            $dados_notificacao = [
                'contador_nome' => $temp['nome'],
                'usuario_nome' => $temp['usuario_nome'],
                'data_hora' => date('d/m/Y H:i:s')
            ];
            enviar_email($usuario['email'], 'Contador acessou suas notas', $dados_notificacao, 'email_notificacao_contador_logado.php');
        }

        unset($_SESSION['contador_temp']);
        header('Location: contador_dashboard.php');
        exit;
    } else {
        $erro = 'Código inválido ou expirado.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Verificar Código Contador</title>
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
            <a href="contador_login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-shield-alt"></i> Verificação 2FA</h2>
            <p style="text-align:center; color:#aaa; margin-bottom:20px;">
                Enviamos um código para o e-mail do contador.
            </p>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= $erro ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="codigo"><i class="fas fa-key"></i> Código</label>
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
