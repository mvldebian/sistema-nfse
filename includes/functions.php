<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function gerar_codigo() {
    return sprintf("%06d", mt_rand(1, 999999));
}

function codigo_valido($pdo, $cpf_cnpj, $codigo) {
    $stmt = $pdo->prepare("SELECT codigo_expiracao FROM usuarios WHERE cpf_cnpj = ?");
    $stmt->execute([$cpf_cnpj]);
    $row = $stmt->fetch();
    if (!$row) return false;
    $expiracao = new DateTime($row['codigo_expiracao']);
    $agora = new DateTime();
    return ($agora <= $expiracao);
}

function criar_pasta_usuario($cpf_cnpj) {
    $pasta_nome = sanitizar_pasta($cpf_cnpj);
    $caminho = UPLOAD_DIR . $pasta_nome;
    if (!is_dir($caminho)) {
        mkdir($caminho, 0755, true);
    }
    return $pasta_nome;
}

function enviar_email($destino, $assunto, $dados = [], $template = 'email_codigo.php') {
    if (!defined('MAIL_HOST') || !defined('MAIL_USERNAME')) {
        error_log("Constantes de e-mail não definidas em config.php");
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($destino);
        $mail->isHTML(true);
        $mail->Subject = $assunto;

        $template_path = __DIR__ . '/../templates/' . $template;
        if (!file_exists($template_path)) {
            error_log("Template não encontrado: " . $template_path);
            return false;
        }

        ob_start();
        include $template_path;
        $body = ob_get_clean();

        if (empty($body)) {
            error_log("Corpo do e-mail vazio.");
            return false;
        }

        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}

function formatar_tamanho($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function validar_turnstile($token) {
    if (!TURNSTILE_ENABLED) return true;
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => TURNSTILE_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === false) return false;
    $json = json_decode($result, true);
    return isset($json['success']) && $json['success'] === true;
}

function contar_arquivos_por_extensao($pasta, $extensao) {
    $caminho = UPLOAD_DIR . $pasta;
    if (!is_dir($caminho)) return 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($caminho));
    $count = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === $extensao) {
            $count++;
        }
    }
    return $count;
}

function calcular_tamanho_pasta($caminho) {
    if (!is_dir($caminho)) return 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($caminho));
    $total = 0;
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $total += $file->getSize();
        }
    }
    return $total;
}
?>
