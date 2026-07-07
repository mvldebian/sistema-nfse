<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sistema NFS-e - Código de Acesso Contador</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7fa; padding:20px; }
        .container { max-width:600px; margin:0 auto; background:#fff; padding:30px; border-radius:12px; border:1px solid #e6edf4; }
        .header { text-align:center; border-bottom:2px solid #0d2b45; padding-bottom:15px; }
        .codigo { font-size:32px; font-weight:700; color:#0d2b45; background:#e3edf7; padding:15px; text-align:center; border-radius:8px; letter-spacing:4px; }
        .footer { margin-top:30px; font-size:12px; color:#888; text-align:center; border-top:1px solid #eee; padding-top:15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color:#0d2b45;">Sistema NFS-e</h1>
            <p style="color:#666;">Permissão de Acesso para a Contabilidade</p>
        </div>
        <p>Olá, <strong><?= htmlspecialchars($dados['nome']) ?></strong>!</p>
        <p>Seu código de acesso é:</p>
        <div class="codigo"><?= $dados['codigo'] ?></div>
        <p style="margin-top:20px;">⏳ Válido por <strong>5 minutos</strong>.</p>
        <p>Este código foi gerado para acessar as notas fiscais de um cliente.</p>
        <div class="footer">Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.</div>
    </div>
</body>
</html>
