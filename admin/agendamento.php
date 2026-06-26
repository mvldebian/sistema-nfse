<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$erro = '';
$email_teste = '';

// Busca todos os usuários (para exibir quantidade)
$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE is_admin = 0");
$total_usuarios = $stmt->fetch()['total'];

// Processa envio para todos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_todos'])) {
    // Busca todos os emails de usuários não admin
    $stmt = $pdo->query("SELECT id, nome, email FROM usuarios WHERE is_admin = 0");
    $usuarios = $stmt->fetchAll();
    
    $enviados = 0;
    $falhas = 0;
    
    foreach ($usuarios as $user) {
        $dados_email = [
            'nome' => $user['nome']
        ];
        $enviado = enviar_email(
            $user['email'],
            'Sistema NFS-e - Notas Fiscais Disponíveis',
            $dados_email,
            'email_notificacao_mensal.php'
        );
        if ($enviado) {
            $enviados++;
        } else {
            $falhas++;
        }
        // Pequena pausa para evitar sobrecarga
        usleep(50000);
    }
    
    $mensagem = "Notificação enviada para $enviados usuários. Falhas: $falhas.";
}

// Processa envio de teste
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_teste'])) {
    $email_teste = trim($_POST['email_teste'] ?? '');
    
    if (empty($email_teste) || !filter_var($email_teste, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail de teste inválido.';
    } else {
        $dados_email = [
            'nome' => 'Usuário de Teste'
        ];
        $enviado = enviar_email(
            $email_teste,
            '[TESTANDO] Sistema NFS-e - Notas Fiscais Disponíveis',
            $dados_email,
            'email_notificacao_mensal.php'
        );
        if ($enviado) {
            $mensagem = "E-mail de teste enviado para $email_teste com sucesso!";
        } else {
            $erro = 'Erro ao enviar e-mail de teste. Verifique as configurações de SMTP.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e Admin - Agendamento</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-user-cog"></i> Painel Administrativo</div>
        <nav>
            <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
            <a href="perfil.php"><i class="fas fa-user"></i> Meu Perfil</a>
            <a href="create_user.php"><i class="fas fa-user-plus"></i> Novo Usuário</a>
            <a href="browse.php"><i class="fas fa-folder-open"></i> Pastas</a>
            <a href="agendamento.php"><i class="fas fa-calendar-alt"></i> Agendamento</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width:750px;">
            <h2><i class="fas fa-calendar-alt"></i> Agendamento Mensal</h2>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div style="background:#e3edf7; border-radius:12px; padding:20px; margin-bottom:25px;">
                <p><i class="fas fa-info-circle"></i> O total de <strong><?= $total_usuarios ?></strong> usuários ativos receberão a notificação Mensal.</p>
                <p style="font-size:0.9rem; color:#555; margin-top:6px;">
                    O envio automático ocorre todo dia em que estiver <strong>defino no Crontab</strong>.
                </p>
            </div>

            <form method="POST">
                <button type="submit" name="enviar_todos" class="btn btn-block" style="background:#0d2b45;" onclick="return confirm('Enviar notificação para todos os usuários?');">
                    <i class="fas fa-paper-plane"></i> Enviar para Todos</button>
            </form>

            <hr style="margin:30px 0; border-color:#eee;">

            <form method="POST">
                <div class="form-group">
                    <label for="email_teste"><i class="fas fa-envelope"></i> E-mail de Teste</label>
                    <input type="email" name="email_teste" id="email_teste" value="<?= htmlspecialchars($email_teste) ?>" placeholder="digite@email.com" required>
                </div>
                <button type="submit" name="enviar_teste" class="btn btn-block" style="background:#e65100;">
                    <i class="fas fa-paper-plane"></i> Enviar Teste
                </button>
            </form>

            <p style="text-align:center; margin-top:25px; font-size:0.9rem; color:#888;">
                <i class="fas fa-clock"></i> O agendamento automático requer configuração de Crontab.
            </p>
            
            <br><br>

 <div style="background:#e3edf7; border-radius:12px; padding:20px; margin-bottom:25px;">
                <p><i class="fas fa-info-circle"></i> Exemplo de Crontab executando todo dia 1º de cada Mês:</p><br>
                <p style="font-size:0.9rem; color:#555; margin-top:6px;">0 9 1 * * /usr/bin/php /var/www/html/nfse/cron/envio_mensal.php</p>
            </div>

       </div>
    </div>

    <footer>
        <i class="fas fa-user-cog"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
