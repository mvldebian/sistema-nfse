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

// ===== PROCESSAR AÇÕES =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

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
            $mensagem = 'Usuário e pasta removidos com Sucesso.';
        } else {
            $erro = 'Erro ao excluir Usuário.';
        }
    }

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

// ===== MÉTRICAS =====
// Total de usuários
$stmtTotal = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE is_admin = 0");
$totalUsuarios = $stmtTotal->fetchColumn();

// Usuários ativos
$stmtAtivos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE is_admin = 0 AND ativo = 1");
$totalAtivos = $stmtAtivos->fetchColumn();

// Pastas criadas (distintas)
$stmtPastas = $pdo->query("SELECT COUNT(DISTINCT pasta) FROM usuarios WHERE is_admin = 0");
$totalPastas = $stmtPastas->fetchColumn();

// Total de XMLs e PDFs em TODAS as pastas dos usuários
$totalXml = 0;
$totalPdf = 0;

// Busca todas as pastas de usuários
$stmtPastasLista = $pdo->query("SELECT pasta FROM usuarios WHERE is_admin = 0");
$pastas = $stmtPastasLista->fetchAll(PDO::FETCH_COLUMN);

foreach ($pastas as $pasta) {
    $caminho = UPLOAD_DIR . $pasta;
    if (is_dir($caminho)) {
        $totalXml += contar_arquivos_por_extensao($pasta, 'xml');
        $totalPdf += contar_arquivos_por_extensao($pasta, 'pdf');
    }
}

// ===== PAGINAÇÃO E BUSCA =====
$porPagina = 15;
$paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $porPagina;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

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
$totalUsuariosFiltrados = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalUsuariosFiltrados / $porPagina);

$sqlLista .= " ORDER BY created_at DESC LIMIT " . intval($porPagina) . " OFFSET " . intval($offset);
$stmt = $pdo->prepare($sqlLista);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

if (isset($_SESSION['admin_mensagem'])) {
    $mensagem = $_SESSION['admin_mensagem'];
    unset($_SESSION['admin_mensagem']);
}
if (isset($_SESSION['admin_erro'])) {
    $erro = $_SESSION['admin_erro'];
    unset($_SESSION['admin_erro']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
    <style>
        /* Largura expandida */
        .container {
            max-width: 1400px !important;
            padding: 30px 25px;
        }
        .card {
            max-width: 100% !important;
            padding: 30px;
        }

        /* Cards de métricas coloridos */
        .metricas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .metrica-card {
            border-radius: 16px;
            padding: 22px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .metrica-card:hover {
            transform: translateY(-4px);
        }
        .metrica-card .icone {
            font-size: 2.4rem;
            opacity: 0.7;
        }
        .metrica-card .info {
            flex: 1;
        }
        .metrica-card .numero {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .metrica-card .rotulo {
            font-size: 0.9rem;
            opacity: 0.85;
        }
        .metrica-card.azul {
            background: linear-gradient(135deg, #0d2b45, #1a3a5c);
        }
        .metrica-card.verde {
            background: linear-gradient(135deg, #1b5e20, #2e7d32);
        }
        .metrica-card.laranja {
            background: linear-gradient(135deg, #bf360c, #e65100);
        }
        .metrica-card.roxo {
            background: linear-gradient(135deg, #4a148c, #6a1b9a);
        }
        .metrica-card.vermelho {
            background: linear-gradient(135deg, #b71c1c, #c62828);
        }
        body.tema-escuro .metrica-card.azul {
            background: linear-gradient(135deg, #0a1a2a, #0d2b45);
        }
        body.tema-escuro .metrica-card.verde {
            background: linear-gradient(135deg, #0a1a0a, #1b3a1b);
        }
        body.tema-escuro .metrica-card.laranja {
            background: linear-gradient(135deg, #2a1a0a, #3a1a0a);
        }
        body.tema-escuro .metrica-card.roxo {
            background: linear-gradient(135deg, #1a0a2a, #2a0a3a);
        }
        body.tema-escuro .metrica-card.vermelho {
            background: linear-gradient(135deg, #2a0a0a, #3a0a0a);
        }

        /* Tabela e ações (mesmo do create_user) */
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
        @media (max-width: 768px) {
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
            .metricas-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 12px;
            }
            .metrica-card {
                padding: 14px 12px;
            }
            .metrica-card .numero {
                font-size: 1.5rem;
            }
            .metrica-card .icone {
                font-size: 1.8rem;
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
        <div class="card" style="padding:30px;">
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>

            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- MÉTRICAS COLORIDAS -->
            <div class="metricas-grid">
                <div class="metrica-card azul">
                    <div class="icone"><i class="fas fa-users"></i></div>
                    <div class="info">
                        <div class="numero"><?= $totalUsuarios ?></div>
                        <div class="rotulo">Total de Usuários</div>
                    </div>
                </div>
                <div class="metrica-card verde">
                    <div class="icone"><i class="fas fa-user-check"></i></div>
                    <div class="info">
                        <div class="numero"><?= $totalAtivos ?></div>
                        <div class="rotulo">Usuários Ativos</div>
                    </div>
                </div>
                <div class="metrica-card laranja">
                    <div class="icone"><i class="fas fa-folder-open"></i></div>
                    <div class="info">
                        <div class="numero"><?= $totalPastas ?></div>
                        <div class="rotulo">Pastas Criadas</div>
                    </div>
                </div>
                <div class="metrica-card roxo">
                    <div class="icone"><i class="fas fa-file-code"></i></div>
                    <div class="info">
                        <div class="numero"><?= $totalXml ?></div>
                        <div class="rotulo">XML's</div>
                    </div>
                </div>
                <div class="metrica-card vermelho">
                    <div class="icone"><i class="fas fa-file-pdf"></i></div>
                    <div class="info">
                        <div class="numero"><?= $totalPdf ?></div>
                        <div class="rotulo">DANFe's</div>
                    </div>
                </div>
            </div>

            <!-- BUSCA -->
            <div class="search-box">
                <form method="GET" style="display:flex; gap:10px; flex:1; flex-wrap:wrap;">
                    <input type="text" name="busca" placeholder="🔍 Buscar por nome, CPF/CNPJ ou e-mail..." value="<?= htmlspecialchars($busca) ?>">
                    <button type="submit" class="btn"><i class="fas fa-search"></i> Buscar</button>
                    <?php if (!empty($busca)): ?>
                        <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- LISTA DE USUÁRIOS -->
            <div class="table-responsive">
                <table>
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
                                    <p style="color:#888; margin-top:5px;">Nenhum usuário Encontrado.</p>
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
                                        <a href="create_user.php?edit=<?= $u['id'] ?>&busca=<?= urlencode($busca) ?>&pagina=<?= $paginaAtual ?>" class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Excluir este Usuário e sua Pasta?')">
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

            <!-- PAGINAÇÃO -->
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
                Mostrando <?= count($usuarios) ?> de <?= $totalUsuariosFiltrados ?> Usuário(s)
                <?php if (!empty($busca)): ?>
                    (filtrado por "<?= htmlspecialchars($busca) ?>")
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <i class="fas fa-user-cog"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
