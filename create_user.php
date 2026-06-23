<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $cpf_cnpj = preg_replace('/[^0-9]/', '', $_POST['cpf_cnpj']);
    $email = trim($_POST['email']);

    if (empty($nome) || empty($cpf_cnpj) || empty($email)) {
        $erro = 'Todos os campos são obrigatórios.';
    } else {
        // Verifica se já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf_cnpj = ? OR email = ?");
        $stmt->execute([$cpf_cnpj, $email]);
        if ($stmt->rowCount() > 0) {
            $erro = 'CPF/CNPJ ou e-mail já cadastrados.';
        } else {
            // Cria a pasta do usuário
            $pasta_nome = sanitizar_pasta($cpf_cnpj);
            $caminho_pasta = UPLOAD_DIR . $pasta_nome;
            if (!is_dir($caminho_pasta)) {
                mkdir($caminho_pasta, 0755, true);
            }

            // Insere no banco
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf_cnpj, email, pasta) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nome, $cpf_cnpj, $email, $pasta_nome])) {
                $mensagem = 'Usuário cadastrado com sucesso!';
            } else {
                $erro = 'Erro ao cadastrar usuário.';
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
    <title>Cadastrar Usuário</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-folder-open"></i> Meus Arquivos</div>
        <nav>
            <a href="index.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="create_user.php"><i class="fas fa-user-plus"></i> Cadastrar</a>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> Novo Usuário</h2>
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $mensagem ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $erro ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome completo</label>
                    <input type="text" name="nome" id="nome" required>
                </div>
                <div class="form-group">
                    <label for="cpf_cnpj"><i class="fas fa-id-card"></i> CPF ou CNPJ (somente números)</label>
                    <input type="text" name="cpf_cnpj" id="cpf_cnpj" placeholder="Ex: 12345678901" required>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <button type="submit" class="btn btn-block"><i class="fas fa-save"></i> Cadastrar</button>
            </form>
            <p style="text-align:center; margin-top:20px;">
                <a href="index.php" style="color:#a8e6cf;">Já tem cadastro? Faça login</a>
            </p>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Sistema de Arquivos - Todos os direitos reservados.
    </footer>
</body>
</html>
