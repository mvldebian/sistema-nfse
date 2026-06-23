<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/admin_check.php'; // verifica se é admin

// Buscar todos os usuários
$stmt = $pdo->query("SELECT id, nome, cpf_cnpj, email, pasta FROM usuarios ORDER BY nome");
$usuarios = $stmt->fetchAll();

// Navegação de pastas (admin pode ver pastas de qualquer usuário)
$usuario_id_visualizado = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;
$diretorio_atual = isset($_GET['dir']) ? $_GET['dir'] : '';

$caminho_base = '';
$pasta_usuario = '';
if ($usuario_id_visualizado > 0) {
    $stmt = $pdo->prepare("SELECT pasta FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id_visualizado]);
    $row = $stmt->fetch();
    if ($row) {
        $pasta_usuario = $row['pasta'];
        $caminho_base = UPLOAD_DIR . $pasta_usuario;
    }
}

$itens = [];
$caminho_completo = '';
if ($caminho_base) {
    $caminho_completo = realpath($caminho_base . DIRECTORY_SEPARATOR . $diretorio_atual);
    if (strpos($caminho_completo, realpath($caminho_base)) !== 0) {
        $caminho_completo = $caminho_base;
        $diretorio_atual = '';
    }
    $itens = scandir($caminho_completo);
    $itens = array_diff($itens, array('.', '..'));
    // Ordenar
    $pastas = [];
    $arquivos = [];
    foreach ($itens as $item) {
        if (is_dir($caminho_completo . DIRECTORY_SEPARATOR . $item)) {
            $pastas[] = $item;
        } else {
            $arquivos[] = $item;
        }
    }
    sort($pastas);
    sort($arquivos);
    $itens = array_merge($pastas, $arquivos);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-folder-open"></i> Admin - Sistema</div>
        <nav>
            <span style="color:#000; font-weight:600; margin-right:15px;">
                <i class="fas fa-user-shield"></i> Admin
            </span>
            <a href="admin.php"><i class="fas fa-tachometer-alt"></i> Painel</a>
            <a href="admin_create_user.php"><i class="fas fa-user-plus"></i> Novo Usuário</a>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Meus Arquivos</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </header>

    <div class="container">
        <div style="display:flex; flex-wrap:wrap; gap:30px;">
            <!-- Lista de usuários -->
            <div style="flex:1; min-width:250px; background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #333;">
                <h3 style="color:#a8e6cf;"><i class="fas fa-users"></i> Usuários</h3>
                <ul style="list-style:none; padding:0; margin-top:15px;">
                    <?php foreach ($usuarios as $u): ?>
                        <li style="padding:8px 0; border-bottom:1px solid #2a2a2a;">
                            <a href="?usuario_id=<?= $u['id'] ?>&dir=" style="color:#a8e6cf; text-decoration:none;">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($u['nome']) ?>
                                <span style="color:#888; font-size:0.8rem;">(<?= htmlspecialchars($u['cpf_cnpj']) ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Conteúdo da pasta do usuário selecionado -->
            <div style="flex:3; background:#1a1a1a; border-radius:12px; padding:20px; border:1px solid #333;">
                <?php if ($usuario_id_visualizado > 0 && $caminho_base): ?>
                    <h3 style="color:#a8e6cf;">
                        <i class="fas fa-folder-open"></i> Arquivos de 
                        <?php 
                        // Buscar nome do usuário
                        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
                        $stmt->execute([$usuario_id_visualizado]);
                        $nome_user = $stmt->fetchColumn();
                        echo htmlspecialchars($nome_user);
                        ?>
                    </h3>
                    
                    <div style="margin:15px 0; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <span style="color:#aaa;">Pasta:</span>
                        <span style="background:#2a2a2a; padding:5px 15px; border-radius:20px;">
                            <?= $diretorio_atual ? htmlspecialchars($diretorio_atual) : 'Raiz' ?>
                        </span>
                        <?php if ($diretorio_atual): ?>
                            <a href="?usuario_id=<?= $usuario_id_visualizado ?>&dir=<?= urlencode(dirname($diretorio_atual)) ?>" class="btn" style="padding:5px 15px; font-size:0.9rem;">
                                <i class="fas fa-arrow-up"></i> Subir
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($itens)): ?>
                        <div class="alert alert-info">Esta pasta está vazia.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr><th>Nome</th><th>Tamanho</th><th>Ações</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <?php
                                    $caminho_item = $caminho_completo . DIRECTORY_SEPARATOR . $item;
                                    $is_dir = is_dir($caminho_item);
                                    $icone = $is_dir ? 'fa-folder' : 'fa-file';
                                    $tamanho = $is_dir ? '-' : formatar_tamanho(filesize($caminho_item));
                                    if ($is_dir) {
                                        $link = "?usuario_id=$usuario_id_visualizado&dir=" . urlencode($diretorio_atual ? $diretorio_atual . '/' . $item : $item);
                                    } else {
                                        // Para admin, também pode baixar? Sim, via download.php, mas precisamos passar o usuário
                                        // Vamos criar um download_admin.php? ou modificar download.php para aceitar ?user_id
                                        // Por simplicidade, faremos download via download.php?file=... e o usuário logado é admin, 
                                        // mas download.php verifica se o arquivo está na pasta do admin. Então precisamos de um download_admin.php
                                        // Vou criar download_admin.php
                                        $link = "download_admin.php?usuario_id=$usuario_id_visualizado&file=" . urlencode($diretorio_atual ? $diretorio_atual . '/' . $item : $item);
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <i class="fas <?= $icone ?> icone"></i>
                                            <?php if ($is_dir): ?>
                                                <a href="<?= $link ?>"><?= htmlspecialchars($item) ?></a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($item) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $tamanho ?></td>
                                        <td>
                                            <?php if (!$is_dir): ?>
                                                <a href="<?= $link ?>" class="btn" style="padding:3px 12px; font-size:0.8rem;">
                                                    <i class="fas fa-download"></i> Baixar
                                                </a>
                                            <?php else: ?>
                                                <span style="color:#666;">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">Selecione um usuário à esquerda para visualizar seus arquivos.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Sistema de Arquivos - Administração
    </footer>
</body>
</html>

<?php
function formatar_tamanho($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}
?>
