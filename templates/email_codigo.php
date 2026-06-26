<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificação</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .codigo {
            font-size: 32px;
            font-weight: bold;
            color: #0d2b45;
            background: #e8f5e9;
            padding: 15px;
            text-align: center;
            border-radius: 6px;
            letter-spacing: 4px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .destaque {
            color: #1a5a3a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 Olá, <?= htmlspecialchars($dados['nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p>Você solicitou acesso ao <strong>Sistema NFS-e</strong>. Utilize o código abaixo para concluir o login:</p>
        <div class="codigo"><?= $dados['codigo'] ?? '000000' ?></div>
        <p style="margin-top: 20px;">⏳ Este código é válido por <strong>5 minutos</strong>.</p>
        <p>Se você não solicitou este código, ignore este e-mail.</p>
        <div class="footer">
            Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
        </div>
    </div>
</body>
</html>
