<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$mensagem = '';
$erro = '';

// Busca dados atuais
$stmt = $pdo->prepare("SELECT id, nome, email, cpf_cnpj FROM usuarios WHERE id = ? AND is_admin = 1");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION['admin_erro'] = 'Administrador não Encontrado.';
    header('Location: dashboard.php');
    exit;
}

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validação básica
    if (empty($nome) || empty($email)) {
        $erro = 'Nome e e-mail são Obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } else {
        // Verifica se o e-mail já está em uso por outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $admin_id]);
        if ($stmt->rowCount() > 0) {
            $erro = 'Este e-mail já está Cadastrado.';
        } else {
            // Atualiza nome e e-mail
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $admin_id]);
            
            // Se solicitou alteração de senha
            if (!empty($nova_senha) || !empty($confirmar_senha) || !empty($senha_atual)) {
                if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
                    $erro = 'Para alterar a senha, preencha todos os campos.';
                } elseif ($nova_senha !== $confirmar_senha) {
                    $erro = 'A nova senha e a confirmação não coincidem.';
                } elseif (strlen($nova_senha) < 6) {
                    $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
                } else {
                    // Verifica a senha atual
                    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $admin_data = $stmt->fetch();
                    
                    if (!password_verify($senha_atual, $admin_data['senha'])) {
                        $erro = 'Senha atual Incorreta.';
                    } else {
                        // Atualiza a senha
                        $hash_nova = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                        $stmt->execute([$hash_nova, $admin_id]);
                        $mensagem = 'Dados atualizados com Sucesso!';
                    }
                }
            } else {
                // Apenas nome e e-mail foram alterados
                $mensagem = 'Dados atualizados com Sucesso!';
            }
            
            // Se não houve erro, atualiza a sessão
            if (empty($erro)) {
                $_SESSION['admin_nome'] = $nome;
                $_SESSION['admin_email'] = $email;
                // Recarrega os dados para exibir
                $admin['nome'] = $nome;
                $admin['email'] = $email;
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
    <title>NFS-e Admin - Meu Perfil</title>
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
        <div class="card" style="max-width:600px;">
            <h2><i class="fas fa-user-edit"></i> Meu Perfil</h2>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome Completo</label>
                    <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($admin['nome']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="cpf_cnpj"><i class="fas fa-id-card"></i> CPF/CNPJ (fixo)</label>
                    <input type="text" id="cpf_cnpj" value="<?= htmlspecialchars($admin['cpf_cnpj']) ?>" disabled style="background:#e9ecef; cursor:not-allowed;">
                    <small style="color:#888;">Este campo não pode ser alterado.</small>
                </div>

                <hr style="margin:25px 0; border-color:#eee;">

                <h3 style="color:#0d2b45; margin-bottom:15px; font-size:1.1rem;"><i class="fas fa-key"></i> Alterar Senha</h3>
                <p style="color:#666; font-size:0.9rem; margin-bottom:15px;">Preencha apenas se desejar trocar a senha.</p>

                <div class="form-group">
                    <label for="senha_atual"><i class="fas fa-lock"></i> Senha Atual</label>
                    <input type="password" name="senha_atual" id="senha_atual" placeholder="Digite sua senha atual">
                </div>

                <div class="form-group">
                    <label for="nova_senha"><i class="fas fa-lock"></i> Nova Senha</label>
                    <input type="password" name="nova_senha" id="nova_senha" placeholder="Nova senha (mínimo 6 caracteres)">
                </div>

                <div class="form-group">
                    <label for="confirmar_senha"><i class="fas fa-check-circle"></i> Confirmar nova senha</label>
                    <input type="password" name="confirmar_senha" id="confirmar_senha" placeholder="Digite novamente a nova senha">
                </div>

                <button type="submit" class="btn btn-block"><i class="fas fa-save"></i> Salvar Alterações</button>
            </form>
            </p>
        </div>
    </div>

    <footer>
        <i class="fas fa-user-cog"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
