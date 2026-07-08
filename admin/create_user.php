<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$erro = '';
$modo = 'add'; // add | edit
$edit_id = 0;
$nome = '';
$cpf_cnpj = '';
$email = '';
$ativo = 1;

// Processa ações POST (criar, editar, excluir, toggle)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // ===== CRIAR USUÁRIO =====
    if ($action === 'add') {
        $nome = trim($_POST['nome']);
        $cpf_cnpj = preg_replace('/[^0-9]/', '', trim($_POST['cpf_cnpj']));
        $email = trim($_POST['email']);
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (empty($nome) || empty($cpf_cnpj) || empty($email)) {
            $erro = 'Todos os campos são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf_cnpj = ? OR email = ?");
            $stmt->execute([$cpf_cnpj, $email]);
            if ($stmt->rowCount() > 0) {
                $erro = 'CPF/CNPJ ou e-mail já cadastrados.';
            } else {
                $pasta_nome = sanitizar_pasta($cpf_cnpj);
                $caminho_pasta = UPLOAD_DIR . $pasta_nome;
                if (!is_dir($caminho_pasta)) {
                    mkdir($caminho_pasta, 0755, true);
                }
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf_cnpj, email, pasta, ativo) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$nome, $cpf_cnpj, $email, $pasta_nome, $ativo])) {
                    $mensagem = 'Usuário cadastrado com sucesso!';
                    $nome = $cpf_cnpj = $email = '';
                    $ativo = 1;
                } else {
                    $erro = 'Erro ao cadastrar usuário.';
                }
            }
        }
    }

    // ===== EDITAR USUÁRIO =====
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $cpf_cnpj = preg_replace('/[^0-9]/', '', trim($_POST['cpf_cnpj']));
        $email = trim($_POST['email']);
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (empty($nome) || empty($cpf_cnpj) || empty($email)) {
            $erro = 'Todos os campos são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE (cpf_cnpj = ? OR email = ?) AND id != ?");
            $stmt->execute([$cpf_cnpj, $email, $id]);
            if ($stmt->rowCount() > 0) {
                $erro = 'CPF/CNPJ ou e-mail já cadastrados para outro usuário.';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, cpf_cnpj = ?, email = ?, ativo = ? WHERE id = ?");
                if ($stmt->execute([$nome, $cpf_cnpj, $email, $ativo, $id])) {
                    // Renomeia a pasta se CPF mudou
                    $stmtPasta = $pdo->prepare("SELECT pasta FROM usuarios WHERE id = ?");
                    $stmtPasta->execute([$id]);
                    $pastaAtual = $stmtPasta->fetchColumn();
                    $novaPasta = sanitizar_pasta($cpf_cnpj);
                    if ($pastaAtual !== $novaPasta) {
                        $caminhoAntigo = UPLOAD_DIR . $pastaAtual;
                        $caminhoNovo = UPLOAD_DIR . $novaPasta;
                        if (is_dir($caminhoAntigo)) {
                            rename($caminhoAntigo, $caminhoNovo);
                        }
                        $stmtUpdatePasta = $pdo->prepare("UPDATE usuarios SET pasta = ? WHERE id = ?");
                        $stmtUpdatePasta->execute([$novaPasta, $id]);
                    }
                    $mensagem = 'Usuário atualizado com sucesso!';
                    $modo = 'add';
                    $edit_id = 0;
                    $nome = $cpf_cnpj = $email = '';
                    $ativo = 1;
                } else {
                    $erro = 'Erro ao atualizar usuário.';
                }
            }
        }
    }

    // ===== EXCLUIR USUÁRIO =====
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT pasta FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $pasta = $stmt->fetchColumn();
        if ($pasta) {
            $caminho = UPLOAD_DIR . $pasta;
            if (is_dir($caminho)) {
                remover_pasta($caminho);
            }
        }
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        if ($stmt->execute([$id])) {
            $mensagem = 'Usuário e sua pasta removidos com sucesso.';
        } else {
            $erro = 'Erro ao excluir usuário.';
        }
    }

    // ===== ALTERNAR STATUS =====
    if ($action === 'toggle') {
        $id = intval($_POST['id']);
        $novoStatus = intval($_POST['ativo']);
        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = ? WHERE id = ?");
        if ($stmt->execute([$novoStatus, $id])) {
            $mensagem = 'Status alterado com sucesso.';
        } else {
            $erro = 'Erro ao alterar status.';
        }
    }
}

// Carrega dados para edição via GET
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT id, nome, cpf_cnpj, email, ativo FROM usuarios WHERE id = ?");
    $stmt->execute([$edit_id]);
    $usuario = $stmt->fetch();
    if ($usuario) {
        $modo = 'edit';
        $nome = $usuario['nome'];
        $cpf_cnpj = $usuario['cpf_cnpj'];
        $email = $usuario['email'];
        $ativo = $usuario['ativo'];
    } else {
        $erro = 'Usuário não encontrado.';
        $modo = 'add';
        $edit_id = 0;
    }
}

// ===== PAGINAÇÃO =====
$porPagina = 15;
$paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $porPagina;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Conta total de usuários (com busca se houver)
$sqlTotal = "SELECT COUNT(*) as total FROM usuarios WHERE is_admin = 0";
$sqlLista = "SELECT id, nome, cpf_cnpj, email, pasta, ativo, created_at FROM usuarios WHERE is_admin = 0";
$params = [];

if (!empty($busca)) {
    $sqlTotal .= " AND (nome LIKE ? OR cpf_cnpj LIKE ? OR email LIKE ?)";
    $sqlLista .= " AND (nome LIKE ? OR cpf_cnpj LIKE ? OR email LIKE ?)";
    $like = '%' . $busca . '%';
    $params = [$like, $like, $like];
}

$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalUsuarios = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalUsuarios / $porPagina);

// Aplica limite e offset
$sqlLista .= " ORDER BY created_at DESC LIMIT " . intval($porPagina) . " OFFSET " . intval($offset);
$stmt = $pdo->prepare($sqlLista);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e Admin - Usuários</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
    <style>
        .table-responsive {
            overflow-x: auto;
            margin-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            min-width: 800px;
        }
        th {
            text-align: left;
            padding: 12px 10px;
            color: #1a2a3a;
            border-bottom: 2px solid #e6edf4;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        td {
            padding: 12px 10px;
            background: #f8fbfe;
            border-radius: 8px;
            vertical-align: middle;
        }
        body.tema-escuro td {
            background: #242b33;
        }
        body.tema-escuro th {
            color: #b0c9e0;
            border-bottom-color: #2a3038;
        }
        .acoes {
            white-space: nowrap;
            text-align: right;
            min-width: 140px;
        }
        .btn-action {
            padding: 6px 12px;
            font-size: 0.8rem;
            margin: 2px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-action i {
            font-size: 0.9rem;
        }
        .btn-edit {
            background: #e3edf7;
            color: #0d2b45;
        }
        .btn-edit:hover {
            background: #d0dce8;
        }
        .btn-delete {
            background: #ffebee;
            color: #c62828;
        }
        .btn-delete:hover {
            background: #f5c6cb;
        }
        .btn-toggle {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .btn-toggle.inativo {
            background: #ffebee;
            color: #c62828;
        }
        .btn-toggle:hover {
            opacity: 0.8;
        }
        body.tema-escuro .btn-edit {
            background: #2a3038;
            color: #e0edf5;
        }
        body.tema-escuro .btn-edit:hover {
            background: #3a434e;
        }
        body.tema-escuro .btn-delete {
            background: #3a1e1e;
            color: #ef9a9a;
        }
        body.tema-escuro .btn-delete:hover {
            background: #4a2a2a;
        }
        body.tema-escuro .btn-toggle {
            background: #1e3a2a;
            color: #81c784;
        }
        body.tema-escuro .btn-toggle.inativo {
            background: #3a1e1e;
            color: #ef9a9a;
        }
        .status-ativo {
            color: #2e7d32;
        }
        .status-inativo {
            color: #c62828;
        }
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-box input {
            padding: 10px 16px;
            border-radius: 40px;
            border: 1px solid #dde7f0;
            flex: 1;
            min-width: 200px;
            background: #f7faff;
            color: #1a2a3a;
        }
        body.tema-escuro .search-box input {
            background: #242b33;
            border-color: #3a434e;
            color: #e0edf5;
        }
        .search-box .btn {
            padding: 10px 20px;
            border-radius: 40px;
        }
        .paginacao {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .paginacao a, .paginacao span {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 30px;
            background: #f8fbfe;
            color: #0d2b45;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border: 1px solid #e6edf4;
            transition: all 0.2s;
        }
        .paginacao a:hover {
            background: #e3edf7;
        }
        .paginacao .ativo {
            background: #0d2b45;
            color: #fff;
            border-color: #0d2b45;
        }
        body.tema-escuro .paginacao a,
        body.tema-escuro .paginacao span {
            background: #242b33;
            color: #e0edf5;
            border-color: #3a434e;
        }
        body.tema-escuro .paginacao a:hover {
            background: #2a323c;
        }
        body.tema-escuro .paginacao .ativo {
            background: #1a3a5c;
            color: #fff;
            border-color: #1a3a5c;
        }
        .paginacao .disabled {
            opacity: 0.5;
            cursor: default;
            pointer-events: none;
        }
        .info-registros {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        body.tema-escuro .info-registros {
            color: #aaa;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        .form-grid .form-group {
            margin-bottom: 0;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .acoes {
                min-width: 100px;
            }
            .btn-action {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
            .btn-action i {
                font-size: 0.8rem;
            }
            .search-box {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-user-cog"></i> Painel Administrativo</div>
        <nav>
            <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
            <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
            <a href="create_user.php"><i class="fas fa-user-plus"></i> Usuários</a>
            <a href="browse.php"><i class="fas fa-folder-open"></i> Pastas</a>
            <a href="agendamento.php"><i class="fas fa-calendar-alt"></i> Agendamento</a>
            <a href="informativos.php"><i class="fas fa-bullhorn"></i> Informativos</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width:1200px; padding:25px;">
            <h2><i class="fas fa-user-plus"></i> Gerenciar Usuários</h2>

            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- FORMULÁRIO -->
            <div style="background:#f5f9fe; border-radius:16px; padding:20px; margin-bottom:30px;">
                <h3><?= $modo === 'edit' ? '✏️ Editar Usuário' : '➕ Novo Usuário' ?></h3><br><br>
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $modo === 'edit' ? 'edit' : 'add' ?>">
                    <?php if ($modo === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $edit_id ?>">
                    <?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nome / Razão Social</label>
                            <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>CPF/CNPJ</label>
                            <input type="text" name="cpf_cnpj" value="<?= htmlspecialchars($cpf_cnpj) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="form-group" style="display:flex; align-items:center; gap:10px; padding-top:24px;">
                            <label style="margin:0;">Ativo</label>
                            <input type="checkbox" name="ativo" value="1" <?= $ativo ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <div style="margin-top:15px; display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="submit" class="btn">
                            <i class="fas <?= $modo === 'edit' ? 'fa-save' : 'fa-plus' ?>"></i> 
                            <?= $modo === 'edit' ? 'Atualizar' : 'Cadastrar' ?>
                        </button>
                        <?php if ($modo === 'edit'): ?>
                            <a href="create_user.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- LISTA DE USUÁRIOS -->
            <h3 style="margin-bottom:10px;"><i class="fas fa-list"></i> Usuários cadastrados</h3>
            <div class="search-box">
                <form method="GET" style="display:flex; gap:10px; flex:1; flex-wrap:wrap;">
                    <input type="text" name="busca" placeholder="🔍 Buscar por nome, CPF/CNPJ ou e-mail..." value="<?= htmlspecialchars($busca) ?>">
                    <button type="submit" class="btn"><i class="fas fa-search"></i> Buscar</button>
                    <?php if (!empty($busca)): ?>
                        <a href="create_user.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table id="tabelaUsuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF/CNPJ</th>
                            <th>E-mail</th>
                            <th>Pasta</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th class="acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:30px; background:transparent;">
                                    <i class="fas fa-info-circle" style="font-size:1.5rem; color:#888;"></i>
                                    <p style="color:#888; margin-top:5px;">Nenhum usuário encontrado.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['nome']) ?></td>
                                    <td><?= htmlspecialchars($u['cpf_cnpj']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['pasta']) ?></td>
                                    <td>
                                        <span class="<?= $u['ativo'] ? 'status-ativo' : 'status-inativo' ?>">
                                            <i class="fas <?= $u['ativo'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                            <?= $u['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                    <td class="acoes">
                                        <a href="?edit=<?= $u['id'] ?>&busca=<?= urlencode($busca) ?>&pagina=<?= $paginaAtual ?>" class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Excluir este usuário e sua pasta?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn-action btn-delete" title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="ativo" value="<?= $u['ativo'] ? 0 : 1 ?>">
                                            <button type="submit" class="btn-action btn-toggle <?= $u['ativo'] ? '' : 'inativo' ?>" title="<?= $u['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="fas <?= $u['ativo'] ? 'fa-pause' : 'fa-play' ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <div class="paginacao">
                    <?php if ($paginaAtual > 1): ?>
                        <a href="?pagina=1&busca=<?= urlencode($busca) ?>"><i class="fas fa-angle-double-left"></i></a>
                        <a href="?pagina=<?= $paginaAtual - 1 ?>&busca=<?= urlencode($busca) ?>"><i class="fas fa-angle-left"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                        <span class="disabled"><i class="fas fa-angle-left"></i></span>
                    <?php endif; ?>

                    <?php
                    $intervalo = 5;
                    $inicio = max(1, $paginaAtual - floor($intervalo / 2));
                    $fim = min($totalPaginas, $inicio + $intervalo - 1);
                    if ($fim - $inicio < $intervalo - 1) {
                        $inicio = max(1, $fim - $intervalo + 1);
                    }
                    ?>
                    <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                        <?php if ($i == $paginaAtual): ?>
                            <span class="ativo"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <a href="?pagina=<?= $paginaAtual + 1 ?>&busca=<?= urlencode($busca) ?>"><i class="fas fa-angle-right"></i></a>
                        <a href="?pagina=<?= $totalPaginas ?>&busca=<?= urlencode($busca) ?>"><i class="fas fa-angle-double-right"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-right"></i></span>
                        <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="info-registros">
                Mostrando <?= count($usuarios) ?> de <?= $totalUsuarios ?> usuário(s)
                <?php if (!empty($busca)): ?>
                    (filtrado por "<?= htmlspecialchars($busca) ?>")
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <i class="fas fa-user-cog"></i> Painel Administrativo &bull; &copy; <?= date('Y') ?>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
