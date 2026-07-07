<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST['email'])) {
    $_SESSION['erro_contador'] = 'E-mail não informado.';
    header('Location: contador_login.php');
    exit;
}

$email = trim($_POST['email']);

// Busca contador ativo
$stmt = $pdo->prepare("SELECT c.id, c.nome, c.usuario_id, u.nome as usuario_nome, u.pasta 
                        FROM contadores c 
                        JOIN usuarios u ON c.usuario_id = u.id 
                        WHERE c.email = ? AND c.ativo = 1");
$stmt->execute([$email]);
$contador = $stmt->fetch();

if (!$contador) {
    $_SESSION['erro_contador'] = 'Contador não encontrado ou desativado.';
    header('Location: contador_login.php');
    exit;
}

// Gera código
$codigo = gerar_codigo();
$expiracao = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Armazena código na sessão (não temos tabela para contador, usamos sessão)
$_SESSION['contador_temp'] = [
    'id' => $contador['id'],
    'nome' => $contador['nome'],
    'usuario_id' => $contador['usuario_id'],
    'usuario_nome' => $contador['usuario_nome'],
    'pasta' => $contador['pasta'],
    'codigo' => $codigo,
    'expiracao' => $expiracao
];

// Envia e-mail com template específico
$dados_email = [
    'nome' => $contador['nome'],
    'codigo' => $codigo
];
$enviado = enviar_email($email, 'Código de acesso - Contador NFS', $dados_email, 'email_contador_codigo.php');

if (!$enviado) {
    $_SESSION['erro_contador'] = 'Erro ao enviar e-mail.';
    header('Location: contador_login.php');
    exit;
}

header('Location: verify_contador.php');
exit;
?>
