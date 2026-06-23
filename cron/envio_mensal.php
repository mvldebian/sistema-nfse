#!/usr/bin/php
<?php
// Script para execução via cron - enviar notificação mensal
// Configurar no crontab: 0 9 1 * * /usr/bin/php /var/www/html/nfse/cron/envio_mensal.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Busca todos os usuários não admin
$stmt = $pdo->query("SELECT id, nome, email FROM usuarios WHERE is_admin = 0");
$usuarios = $stmt->fetchAll();

$enviados = 0;
$falhas = 0;
$log = [];

foreach ($usuarios as $user) {
    $dados_email = [
        'nome' => $user['nome']
    ];
    $enviado = enviar_email(
        $user['email'],
        'NFS-e - Notas Fiscais Disponíveis',
        $dados_email,
        'email_notificacao_mensal.php'
    );
    if ($enviado) {
        $enviados++;
    } else {
        $falhas++;
        $log[] = "Falha para: " . $user['email'];
    }
    usleep(50000);
}

$log[] = "Envio mensal concluído: $enviados enviados, $falhas falhas.";
file_put_contents(__DIR__ . 'agendamento.log', date('d-m-Y H:i:s') . " - " . implode(" | ", $log) . "\n", FILE_APPEND);

echo "OK: $enviados enviados, $falhas falhas.\n";
