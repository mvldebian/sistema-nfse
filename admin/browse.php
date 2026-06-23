<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

$user_pasta = isset($_GET['user']) ? trim($_GET['user']) : '';
$diretorio_atual = isset($_GET['dir']) ? trim($_GET['dir']) : '';

if ($user_pasta) {
    $caminho_base = UPLOAD_DIR . $user_pasta;
    $titulo = "Arquivos do Usuário: $user_pasta";
} else {
    $caminho_base = UPLOAD_DIR;
    $titulo = "Diretório de Uploads";
}

if (!is_dir($caminho_base)) {
    mkdir($caminho_base, 0755, true);
}

$caminho_completo = realpath($caminho_base . DIRECTORY_SEPARATOR . $diretorio_atual);
if ($caminho_completo === false || strpos($caminho_completo, realpath(UPLOAD_DIR)) !== 0) {
    $caminho_completo = $caminho_base;
    $diretorio_atual = '';
}

$itens = scandir($caminho_completo);
$itens = array_diff($itens, array('.', '..'));

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
$itens_ordenados = array_merge($pastas, $arquivos);

// Mensagens
$mensagem = $_SESSION['admin_mensagem'] ?? '';
$erro = $_SESSION['admin_erro'] ?? '';
unset($_SESSION['admin_mensagem'], $_SESSION['admin_erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e Admin - Explorador</title>
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
        <div class="lista-arquivos">
            <h2><i class="fas fa-folder"></i> <?= htmlspecialchars($titulo) ?></h2>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div style="margin-bottom:20px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span style="color:#aaa;">Diretório:</span>
                <span style="background:#000000; color:#ffffff; padding:5px 15px; border-radius:20px; font-weight:bold;">
                    <?= $diretorio_atual ? htmlspecialchars($diretorio_atual) : 'Raiz' ?>
                </span>
                <?php if ($diretorio_atual): ?>
                    <?php
                    // Monta o link para subir com user
                    $subir_params = ['dir' => dirname($diretorio_atual)];
                    if ($user_pasta) $subir_params['user'] = $user_pasta;
                    $subir_url = '?' . http_build_query($subir_params);
                    ?>
                    <a href="<?= $subir_url ?>" class="btn" style="padding:5px 15px;">
                        <i class="fas fa-arrow-up"></i> Voltar
                    </a>
                <?php endif; ?>
                <?php if (!$user_pasta): ?>
                    <a href="browse.php" class="btn" style="padding:5px 15px;">Voltar à Raiz</a>
                <?php else: ?>
                    <a href="browse.php" class="btn" style="padding:5px 15px;">Voltar à Lista de Usuários</a>
                <?php endif; ?>
            </div>

            <?php if (empty($itens_ordenados)): ?>
                <div class="alert alert-info">Esta pasta está Vazia.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tamanho</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens_ordenados as $item): ?>
                            <?php
                            $caminho_item = $caminho_completo . DIRECTORY_SEPARATOR . $item;
                            $is_dir = is_dir($caminho_item);
                            $icone = $is_dir ? 'fa-folder' : 'fa-file';
                            
                            if ($is_dir) {
                                $tamanho = formatar_tamanho(calcular_tamanho_pasta($caminho_item));
                                $link_params = ['dir' => $diretorio_atual ? $diretorio_atual . '/' . $item : $item];
                                if ($user_pasta) $link_params['user'] = $user_pasta;
                                $link = '?' . http_build_query($link_params);
                            } else {
                                $tamanho = formatar_tamanho(filesize($caminho_item));
                                $link = '#';
                            }
                            
                            // URL de exclusão com user sempre incluso (se existir)
                            $delete_params = [
                                'item' => $diretorio_atual ? $diretorio_atual . '/' . $item : $item,
                                'type' => $is_dir ? 'dir' : 'file'
                            ];
                            if ($user_pasta) {
                                $delete_params['user'] = $user_pasta;
                            }
                            $delete_url = 'delete.php?' . http_build_query($delete_params);
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
                                    <a href="<?= $delete_url ?>" class="btn btn-danger" style="background:#c62828; color:#fff; padding:4px 14px; font-size:0.8rem; border-radius:30px;" onclick="return confirm('Tem certeza que deseja excluir \'<?= htmlspecialchars($item) ?>\'? Esta ação não pode ser desfeita.');">
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
