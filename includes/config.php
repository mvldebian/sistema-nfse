<?php
// Inicia a sessão apenas se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'sistemanfse');
define('DB_PASS', 'sistemanfse');
define('DB_NAME', 'sistemanfse');

// Configurações do sistema
define('BASE_URL', 'https://sistemanfse.seudominio.com.br');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('SITE_NAME', 'Sistema de NFS-e');
define('FORCAR_TEMA_CLARO', true);
define('QUOTA_BYTES', 1073741824); // 1 GB

// ===== BACKUP =====
define('BACKUP_DIR', __DIR__ . '/../backups/');
define('TMP_DIR', __DIR__ . '/../tmp/');
define('MAX_BACKUPS', 5);

// Configurações FTP (opcional)
define('FTP_ENABLED', false); // true para ativar envio automático
define('FTP_HOST', 'ftp.seuservidor.com');
define('FTP_USER', 'seuusuario');
define('FTP_PASS', 'suasenha');
define('FTP_PATH', '/backupsnfse/'); // caminho remoto
define('FTP_PORT', 21);

// Cloudflare Turnstile
define('TURNSTILE_ENABLED', false);  // true = ativado, false = desativado
define('TURNSTILE_SITE_KEY', '');
define('TURNSTILE_SECRET_KEY', '');
define('TURNSTILE_THEME', 'light');
define('TURNSTILE_SIZE', 'normal');
define('TURNSTILE_LANGUAGE', 'pt');

// Configurações de e-mail (PHPMailer)
define('MAIL_HOST', 'smtp.seudominio.com.br');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'sistemanfse@seudominio.com.br');
define('MAIL_PASSWORD', 'sistemanfse');
define('MAIL_FROM', 'sistemanfse@seudominio.com.br');
define('MAIL_FROM_NAME', 'Sistema de NFS-e');
define('MAIL_SECURE', 'tls'); // ou 'ssl'

// Conexão com o banco
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para sanitizar CPF/CNPJ
function sanitizar_pasta($cpf_cnpj) {
    return preg_replace('/[^0-9]/', '', $cpf_cnpj);
}

?>
