<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

// ===== FILTROS E ORDENAÇÃO =====
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'id';
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'DESC';

// Valida campos de ordenação
$campos_validos = ['id', 'nome', 'created_at'];
if (!in_array($ordenar, $campos_validos)) {
    $ordenar = 'id';
}
$ordem = strtoupper($ordem) === 'ASC' ? 'ASC' : 'DESC';

// ===== CONSULTA COM FILTRO E ORDENAÇÃO =====
$sql = "SELECT id, nome, cpf_cnpj, email, pasta, created_at FROM usuarios WHERE is_admin = 0";

// Adiciona busca se preenchida
if (!empty($busca)) {
    $busca_like = '%' . $busca . '%';
    $sql .= " AND (nome LIKE :busca OR cpf_cnpj LIKE :busca OR email LIKE :busca)";
}

$sql .= " ORDER BY $ordenar $ordem";

$stmt = $pdo->prepare($sql);
if (!empty($busca)) {
    $stmt->bindParam(':busca', $busca_like);
}
$stmt->execute();
$usuarios = $stmt->fetchAll();

// ===== MENSAGENS =====
$mensagem = $_SESSION['admin_mensagem'] ?? '';
$erro = $_SESSION['admin_erro'] ?? '';
unset($_SESSION['admin_mensagem'], $_SESSION['admin_erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e Admin - Dashboard</title>
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
            <a href="informativos.php"><i class="fas fa-bullhorn"></i> Informativos</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="lista-arquivos">
            <h2><i class="fas fa-users"></i> Usuários Cadastrados</h2>

            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Barra de busca -->
            <form method="GET" style="display:flex; gap:12px; margin-bottom:20px; align-items:center; flex-wrap:wrap;">
                <div style="flex:1; min-width:200px;">
                    <input type="text" name="busca" placeholder="Buscar por nome, CPF/CNPJ ou e-mail..." value="<?= htmlspecialchars($busca) ?>" style="width:100%; padding:10px 16px; border-radius:30px; border:1px solid #dde7f0; background:#f7faff;">
                </div>
                <button type="submit" class="btn" style="padding:10px 24px;"><i class="fas fa-search"></i> Buscar</button>
                <?php if (!empty($busca)): ?>
                    <a href="dashboard.php" class="btn btn-secondary" style="padding:10px 24px;"><i class="fas fa-times"></i> Limpar</a>
                <?php endif; ?>
            </form>

            <!-- Tabela -->
            <?php if (empty($usuarios)): ?>
                <div class="alert alert-info">Nenhum usuário Encontrado.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>
                                <a href="?<?= http_build_query(array_merge($_GET, ['ordenar' => 'id', 'ordem' => ($ordenar == 'id' && $ordem == 'DESC') ? 'ASC' : 'DESC'])) ?>" style="color:inherit; text-decoration:none;">
                                    ID <?= ($ordenar == 'id') ? ($ordem == 'DESC' ? '↓' : '↑') : '' ?>
                                </a>
                            </th>
                            <th>
                                <a href="?<?= http_build_query(array_merge($_GET, ['ordenar' => 'nome', 'ordem' => ($ordenar == 'nome' && $ordem == 'DESC') ? 'ASC' : 'DESC'])) ?>" style="color:inherit; text-decoration:none;">
                                    Nome <?= ($ordenar == 'nome') ? ($ordem == 'DESC' ? '↓' : '↑') : '' ?>
                                </a>
                            </th>
                            <th>CPF/CNPJ</th>
                            <th>E-mail</th>
                            <th>Pasta</th>
                            <th>
                                <a href="?<?= http_build_query(array_merge($_GET, ['ordenar' => 'created_at', 'ordem' => ($ordenar == 'created_at' && $ordem == 'DESC') ? 'ASC' : 'DESC'])) ?>" style="color:inherit; text-decoration:none;">
                                    Data <?= ($ordenar == 'created_at') ? ($ordem == 'DESC' ? '↓' : '↑') : '' ?>
                                </a>
                            </th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nome']) ?></td>
                                <td><?= htmlspecialchars($u['cpf_cnpj']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['pasta']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="browse.php?user=<?= urlencode($u['pasta']) ?>" class="btn" style="padding:3px 10px; font-size:0.8rem;">
                                        <i class="fas fa-folder"></i> Ver Arquivos
                                    </a>
                                    <a href="deletar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-danger" style="background:#c62828; color:#fff; padding:3px 10px; font-size:0.8rem; border-radius:30px;" onclick="return confirm('Tem certeza que deseja excluir o usuário \'<?= htmlspecialchars($u['nome']) ?>\' e todos os seus arquivos? Esta ação não pode ser desfeita.');">
                                        <i class="fas fa-trash"></i> Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <i class="fas fa-user-cog"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
