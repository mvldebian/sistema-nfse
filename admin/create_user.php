<?php
session_start();
if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}
require_once '../includes/config.php';
require_once '../includes/functions.php';

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $cpf_cnpj = preg_replace('/[^0-9]/', '', $_POST['cpf_cnpj']);
    $email = trim($_POST['email']);

    if (empty($nome) || empty($cpf_cnpj) || empty($email)) {
        $erro = 'Todos os campos são Obrigatórios.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf_cnpj = ? OR email = ?");
        $stmt->execute([$cpf_cnpj, $email]);
        if ($stmt->rowCount() > 0) {
            $erro = 'CPF/CNPJ ou e-mail já Cadastrados.';
        } else {
            $pasta_nome = sanitizar_pasta($cpf_cnpj);
            $caminho_pasta = UPLOAD_DIR . $pasta_nome;
            if (!is_dir($caminho_pasta)) {
                mkdir($caminho_pasta, 0755, true);
            }
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf_cnpj, email, pasta) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nome, $cpf_cnpj, $email, $pasta_nome])) {
                $mensagem = 'Usuário cadastrado com Sucesso!';
            } else {
                $erro = 'Erro ao cadastrar Usuário.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e Admin - Novo Usuário</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-user-cog"></i> Painel Administrativo
        </div>
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
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> Cadastrar novo Usuário</h2>
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= $mensagem ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= $erro ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" required>
                </div>
                <div class="form-group">
                    <label>CPF ou CNPJ (somente números)</label>
                    <input type="text" name="cpf_cnpj" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-block"><i class="fas fa-save"></i> Cadastrar</button>
            </form>
        </div>
    </div>

    <footer>
        <i class="fas fa-user-cog"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
