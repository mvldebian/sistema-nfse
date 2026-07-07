<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem = '';
$erro = '';

// Processa formulários (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($action === 'add') {
        if (empty($nome) || empty($email)) {
            $erro = 'Preencha todos os campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM contadores WHERE usuario_id = ? AND email = ?");
            $stmt->execute([$usuario_id, $email]);
            if ($stmt->rowCount() > 0) {
                $erro = 'Este e-mail já está cadastrado para este usuário.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO contadores (usuario_id, nome, email) VALUES (?, ?, ?)");
                if ($stmt->execute([$usuario_id, $nome, $email])) {
                    $mensagem = 'Contador cadastrado com sucesso!';
                } else {
                    $erro = 'Erro ao cadastrar contador.';
                }
            }
        }
    } elseif ($action === 'edit') {
        if ($id > 0 && !empty($nome) && !empty($email)) {
            $stmt = $pdo->prepare("UPDATE contadores SET nome = ?, email = ? WHERE id = ? AND usuario_id = ?");
            if ($stmt->execute([$nome, $email, $id, $usuario_id])) {
                $mensagem = 'Contador atualizado com sucesso!';
            } else {
                $erro = 'Erro ao atualizar contador.';
            }
        } else {
            $erro = 'Dados inválidos para edição.';
        }
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        $ativo = intval($_POST['ativo'] ?? 0);
        $stmt = $pdo->prepare("UPDATE contadores SET ativo = ? WHERE id = ? AND usuario_id = ?");
        if ($stmt->execute([$ativo, $id, $usuario_id])) {
            $mensagem = $ativo ? 'Contador ativado.' : 'Contador desativado.';
        } else {
            $erro = 'Erro ao alterar status.';
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM contadores WHERE id = ? AND usuario_id = ?");
        if ($stmt->execute([$id, $usuario_id])) {
            $mensagem = 'Contador removido com sucesso.';
        } else {
            $erro = 'Erro ao remover contador.';
        }
    }
}

// Lista contadores
$stmt = $pdo->prepare("SELECT * FROM contadores WHERE usuario_id = ? ORDER BY created_at DESC");
$stmt->execute([$usuario_id]);
$contadores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Contador</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
    <style>
        /* Estilos para os modais */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }
        body.tema-escuro .modal-box {
            background: #1a1f26;
            color: #dde7f0;
        }
        .modal-box h3 {
            color: #0d2b45;
            margin-bottom: 20px;
            font-size: 1.4rem;
        }
        body.tema-escuro .modal-box h3 {
            color: #90caf9;
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #888;
            transition: color 0.2s;
            background: none;
            border: none;
        }
        .modal-close:hover {
            color: #333;
        }
        body.tema-escuro .modal-close:hover {
            color: #fff;
        }
        .modal-box .form-group {
            margin-bottom: 18px;
        }
        .modal-box .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #1a2a3a;
        }
        body.tema-escuro .modal-box .form-group label {
            color: #b0c9e0;
        }
        .modal-box .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #dde7f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: border 0.25s;
            background: #f7faff;
            color: #1a2a3a;
        }
        body.tema-escuro .modal-box .form-group input {
            background: #242b33;
            border-color: #3a434e;
            color: #e0edf5;
        }
        .modal-box .form-group input:focus {
            border-color: #0d2b45;
            outline: none;
        }
        body.tema-escuro .modal-box .form-group input:focus {
            border-color: #90caf9;
        }
        .btn-modal {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 40px;
            background: #0d2b45;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-modal:hover {
            background: #1a3a5c;
        }
        body.tema-escuro .btn-modal {
            background: #1a1f26;
            color: #fff;
        }
        body.tema-escuro .btn-modal:hover {
            background: #2a3038;
        }
        .btn-modal-secondary {
            background: #e3edf7;
            color: #0d2b45;
            margin-top: 10px;
        }
        .btn-modal-secondary:hover {
            background: #d0dce8;
        }
        body.tema-escuro .btn-modal-secondary {
            background: #2a3038;
            color: #e0edf5;
        }
        body.tema-escuro .btn-modal-secondary:hover {
            background: #3a434e;
        }
        .modal-box .btn-group {
            display: flex;
            gap: 10px;
        }
        .modal-box .btn-group .btn-modal {
            flex: 1;
        }
        /* Ajustes na lista */
        .contador-linha {
            background: #f8fbfe;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            transition: background 0.2s;
        }
        body.tema-escuro .contador-linha {
            background: #242b33;
        }
        .contador-linha:hover {
            background: #eef4fa;
        }
        body.tema-escuro .contador-linha:hover {
            background: #2a323c;
        }
        .contador-info {
            flex: 1;
            min-width: 180px;
        }
        .contador-info .nome {
            font-weight: 600;
            color: #0d2b45;
        }
        body.tema-escuro .contador-info .nome {
            color: #e0edf5;
        }
        .contador-info .email {
            color: #666;
            font-size: 0.9rem;
        }
        body.tema-escuro .contador-info .email {
            color: #aaa;
        }
        .contador-status {
            margin: 0 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .contador-status.ativo {
            color: #2e7d32;
        }
        .contador-status.inativo {
            color: #c62828;
        }
        .contador-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .contador-actions button {
            border: none;
            background: none;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.2s;
            font-weight: 500;
        }
        .contador-actions .btn-edit {
            background: #e3edf7;
            color: #0d2b45;
        }
        .contador-actions .btn-edit:hover {
            background: #d0dce8;
        }
        .contador-actions .btn-toggle {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .contador-actions .btn-toggle.off {
            background: #ffebee;
            color: #c62828;
        }
        .contador-actions .btn-toggle:hover {
            opacity: 0.8;
        }
        .contador-actions .btn-delete {
            background: #ffebee;
            color: #c62828;
        }
        .contador-actions .btn-delete:hover {
            background: #f5c6cb;
        }
        body.tema-escuro .contador-actions .btn-edit {
            background: #2a3038;
            color: #e0edf5;
        }
        body.tema-escuro .contador-actions .btn-edit:hover {
            background: #3a434e;
        }
        body.tema-escuro .contador-actions .btn-toggle {
            background: #1e3a2a;
            color: #81c784;
        }
        body.tema-escuro .contador-actions .btn-toggle.off {
            background: #3a1e1e;
            color: #ef9a9a;
        }
        body.tema-escuro .contador-actions .btn-delete {
            background: #3a1e1e;
            color: #ef9a9a;
        }
        body.tema-escuro .contador-actions .btn-delete:hover {
            background: #4a2a2a;
        }
        .btn-add-contador {
            background: #0d2b45;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 20px;
        }
        .btn-add-contador:hover {
            background: #1a3a5c;
        }
        body.tema-escuro .btn-add-contador {
            background: #1a1f26;
            color: #fff;
        }
        body.tema-escuro .btn-add-contador:hover {
            background: #2a3038;
        }
        .vazio {
            text-align: center;
            color: #888;
            padding: 30px 0;
        }
        body.tema-escuro .vazio {
            color: #aaa;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-file-invoice"></i> NFS-e
            <small>Nota Fiscal de Serviços</small>
        </div>
        <nav>
            <span><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
            <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
            <a href="contador.php"><i class="fas fa-user-tie"></i> Contador</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width: 900px;">
            <h2><i class="fas fa-user-tie"></i> Gerenciar Contadores</h2>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <button class="btn-add-contador" onclick="abrirModal('modalAdd')">
                <i class="fas fa-plus"></i> Adicionar Contador
            </button>

            <?php if (empty($contadores)): ?>
                <div class="vazio">
                    <i class="fas fa-user-tie" style="font-size:3rem; opacity:0.3; display:block; margin-bottom:10px;"></i>
                    Nenhum contador Cadastrado.
                </div>
            <?php else: ?>
                <?php foreach ($contadores as $c): ?>
                    <div class="contador-linha">
                        <div class="contador-info">
                            <div class="nome"><?= htmlspecialchars($c['nome']) ?></div>
                            <div class="email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($c['email']) ?></div>
                        </div>
                        <div class="contador-status <?= $c['ativo'] ? 'ativo' : 'inativo' ?>">
                            <i class="fas <?= $c['ativo'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                            <?= $c['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </div>
                        <div class="contador-actions">
                            <button class="btn-edit" onclick="editarContador(<?= $c['id'] ?>, '<?= addslashes($c['nome']) ?>', '<?= addslashes($c['email']) ?>')">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="ativo" value="<?= $c['ativo'] ? 0 : 1 ?>">
                                <button type="submit" class="btn-toggle <?= $c['ativo'] ? '' : 'off' ?>">
                                    <i class="fas <?= $c['ativo'] ? 'fa-pause' : 'fa-play' ?>"></i>
                                    <?= $c['ativo'] ? 'Desativar' : 'Ativar' ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Remover este contador?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== MODAL ADICIONAR ===== -->
    <div id="modalAdd" class="modal-overlay">
        <div class="modal-box">
            <button class="modal-close" onclick="fecharModal('modalAdd')">&times;</button>
            <h3><i class="fas fa-user-plus"></i> Adicionar Contador</h3>
            <form method="POST" id="formAdd">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="nome_add">Nome / Razão Social</label>
                    <input type="text" name="nome" id="nome_add" required>
                </div>
                <div class="form-group">
                    <label for="email_add">E-mail</label>
                    <input type="email" name="email" id="email_add" required>
                </div>
                <button type="submit" class="btn-modal"><i class="fas fa-save"></i> Cadastrar</button>
                <button type="button" class="btn-modal btn-modal-secondary" onclick="fecharModal('modalAdd')">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- ===== MODAL EDITAR ===== -->
    <div id="modalEdit" class="modal-overlay">
        <div class="modal-box">
            <button class="modal-close" onclick="fecharModal('modalEdit')">&times;</button>
            <h3><i class="fas fa-user-edit"></i> Editar Contador</h3>
            <form method="POST" id="formEdit">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_nome">Nome / Razão Social</label>
                    <input type="text" name="nome" id="edit_nome" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">E-mail</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <button type="submit" class="btn-modal"><i class="fas fa-save"></i> Atualizar</button>
                <button type="button" class="btn-modal btn-modal-secondary" onclick="fecharModal('modalEdit')">Cancelar</button>
            </form>
        </div>
    </div>

    <footer>
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script>
        // Funções para modais
        function abrirModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function fecharModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        // Fechar ao clicar fora do modal
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Preencher modal de edição
        function editarContador(id, nome, email) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_email').value = email;
            abrirModal('modalEdit');
        }
    </script>

    <script src="assets/js/script.js"></script>
</body>
</html>
