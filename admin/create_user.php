<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $cpf_cnpj = preg_replace('/[^0-9]/', '', $_POST['cpf_cnpj']);
    $email = trim($_POST['email']);
    $enviar_bemvindo = isset($_POST['enviar_bemvindo']) ? true : false;

    if (empty($nome) || empty($cpf_cnpj) || empty($email)) {
        $erro = 'Todos os campos são obrigatórios.';
    } else {
        // Verifica duplicidade
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
                
                // Envia e-mail de boas-vindas se marcado
                if ($enviar_bemvindo) {
                    $dados_email = [
                        'nome' => $nome,
                        'email' => $email,
                        'cpf_cnpj' => $cpf_cnpj
                    ];
                    $enviado = enviar_email(
                        $email,
                        'Bem-vindo ao Sistema NFS-e - Guarda de Notas Fiscais de Serviço',
                        $dados_email,
                        'email_boas_vindas.php'
                    );
                    if ($enviado) {
                        $mensagem .= ' E-mail de boas-vindas enviado!';
                    } else {
                        $mensagem .= ' Falha ao enviar e-mail de boas-vindas.';
                    }
                }
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
    <title>NFS-e Admin - Novo Usuário</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
    <style>
        /* Ajuste para o checkbox */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0 20px;
            padding: 10px 15px;
            background: #f8fbfe;
            border-radius: 10px;
            border: 1px solid #e6edf4;
        }
        body.tema-escuro .checkbox-group {
            background: #1a1f26;
            border-color: #2a3038;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: #0d2b45;
        }
        .checkbox-group label {
            cursor: pointer;
            font-weight: 500;
            color: #1a2a3a;
            margin-bottom: 0;
            flex: 1;
        }
        body.tema-escuro .checkbox-group label {
            color: #dde7f0;
        }
        .checkbox-group .descricao {
            font-weight: 400;
            font-size: 0.9rem;
            color: #666;
        }
        body.tema-escuro .checkbox-group .descricao {
            color: #aaa;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-user-cog"></i> Admin</div>
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
            <h2><i class="fas fa-user-plus"></i> Cadastrar novo Usuário</h2>
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome Completo</label>
                    <input type="text" name="nome" id="nome" required>
                </div>
                <div class="form-group">
                    <label for="cpf_cnpj"><i class="fas fa-id-card"></i> CPF ou CNPJ (somente números)</label>
                    <input type="text" name="cpf_cnpj" id="cpf_cnpj" required>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <!-- Checkbox de boas-vindas (estilizado) -->
                <div class="checkbox-group">
                    <input type="checkbox" name="enviar_bemvindo" id="enviar_bemvindo" checked>
                    <label for="enviar_bemvindo">
                        <i class="fas fa-envelope-open-text"></i> Enviar e-mail de Boas-vindas<br>
                        <span class="descricao">(Apresentação do sistema e recursos)</span>
                    </label>
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
