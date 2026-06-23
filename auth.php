<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verifica se o Turnstile está ativado e valida
if (TURNSTILE_ENABLED) {
    if (empty($_POST['cf-turnstile-response'])) {
        $_SESSION['erro_login'] = 'Por favor, complete a Verificação de Segurança.';
        header('Location: index.php');
        exit;
    }

    $token = $_POST['cf-turnstile-response'];
    $secret = TURNSTILE_SECRET_KEY;
    $ip = $_SERVER['REMOTE_ADDR'];

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $ip
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!$data['success']) {
        $_SESSION['erro_login'] = 'Falha na Verificação de Segurança. Tente novamente.';
        header('Location: index.php');
        exit;
    }
}

// Restante do código (busca usuário, gera código, envia e-mail...)
if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST['cpf_cnpj'])) {
    $_SESSION['erro_login'] = 'CPF/CNPJ não Informado.';
    header('Location: index.php');
    exit;
}

$cpf_cnpj = preg_replace('/[^0-9]/', '', $_POST['cpf_cnpj']);

$stmt = $pdo->prepare("SELECT id, nome, email, pasta FROM usuarios WHERE cpf_cnpj = ?");
$stmt->execute([$cpf_cnpj]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $_SESSION['erro_login'] = 'CPF/CNPJ não Cadastrado.';
    header('Location: index.php');
    exit;
}

$codigo = gerar_codigo();
$expiracao = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$stmt = $pdo->prepare("UPDATE usuarios SET codigo_verificacao = ?, codigo_expiracao = ? WHERE id = ?");
$stmt->execute([$codigo, $expiracao, $usuario['id']]);

$dados_email = [
    'nome' => $usuario['nome'],
    'codigo' => $codigo
];

$enviado = enviar_email($usuario['email'], 'Sistema NFS-e - Seu código de Acesso', $dados_email);

if (!$enviado) {
    $_SESSION['erro_login'] = 'Erro ao enviar e-mail. Verifique as configurações.';
    header('Location: index.php');
    exit;
}

$_SESSION['cpf_tmp'] = $cpf_cnpj;
$_SESSION['usuario_nome'] = $usuario['nome'];

header('Location: verify.php');
exit;
?>
