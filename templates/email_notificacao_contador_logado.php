<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sistema NFS-e - O Contador acessou suas Notas</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7fa; padding:20px; }
        .container { max-width:600px; margin:0 auto; background:#fff; padding:30px; border-radius:12px; border:1px solid #e6edf4; }
        .header { text-align:center; border-bottom:2px solid #0d2b45; padding-bottom:15px; }
        .footer { margin-top:30px; font-size:12px; color:#888; text-align:center; border-top:1px solid #eee; padding-top:15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color:#0d2b45;">Sistema NFS-e</h1>
            <p style="color:#666;">Notificação de Acesso</p>
        </div>
        <br>
        <p>Olá, <strong><?= htmlspecialchars($dados['usuario_nome']) ?></strong>!</p>
        <p>O contador <strong><?= htmlspecialchars($dados['contador_nome']) ?></strong> acessou suas notas fiscais em <strong><?= $dados['data_hora'] ?></strong>.</p>
        <p>Se você não reconhece este acesso, entre em contato com o suporte.</p>
        <div class="footer">Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.</div>
    </div>
</body>
</html>
