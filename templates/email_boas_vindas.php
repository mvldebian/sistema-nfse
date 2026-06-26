<?php
// Array de meses em português
$meses = [
    'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
    'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
    'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
    'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
];
$mes_atual = $meses[date('F')] ?? date('F');
$ano_atual = date('Y');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo ao Sistema NFS-e</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e6edf4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d2b45;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0d2b45;
            font-size: 1.8rem;
            margin: 0;
        }
        .header h1 i {
            color: #1a3a5c;
        }
        .conteudo {
            color: #1a2a3a;
            line-height: 1.7;
        }
        .conteudo p {
            margin: 15px 0;
        }
        .destaque {
            background: #e3edf7;
            border-radius: 8px;
            padding: 15px 20px;
            text-align: center;
            font-weight: 600;
            color: #0d2b45;
            margin: 20px 0;
        }
        .recursos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        .recursos .item {
            background: #f8fbfe;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            color: #0d2b45;
        }
        .recursos .item i {
            color: #0d47a1;
            font-size: 1.2rem;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .btn {
            display: inline-block;
            background: #0d2b45;
            color: #fff;
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn:hover {
            background: #1a3a5c;
        }
        .badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 2px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
        }
        @media (max-width: 500px) {
            .recursos {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-invoice"></i> Sistema NFS-e</h1>
            <p style="color:#666; margin-top:4px;">Guarda de Notas Fiscais de Serviço</p>
        </div>

        <div class="conteudo">
            <p>Olá, <strong><?= htmlspecialchars($dados['nome'] ?? 'Usuário') ?></strong>!</p>

            <p>Seja bem-vindo ao <strong>Sistema NFS-e</strong> – sua plataforma completa para gestão e armazenamento de arquivos fiscais.</p>

            <div class="destaque">
                <i class="fas fa-check-circle" style="color:#2e7d32;"></i> 
                Sua conta foi criada com Sucesso!
            </div>

            <p><strong>Recursos Disponíveis:</strong></p>
            <div class="recursos">
                <div class="item"><i class="fas fa-cloud-upload-alt"></i> Upload e armazenamento</div>
                <div class="item"><i class="fas fa-file-pdf"></i> Visualização de DANFes</div>
                <div class="item"><i class="fas fa-shield-alt"></i> Segurança LGPD</div>
                <div class="item"><i class="fas fa-download"></i> Download de XMLs</div>
                <div class="item"><i class="fas fa-folder-open"></i> Organização por pastas</div>
                <div class="item"><i class="fas fa-history"></i> Backup automático</div>
            </div>

            <p style="text-align:center;">
                <a href="<?= BASE_URL ?>" class="btn"><i class="fas fa-arrow-right"></i> Acessar Sistema</a>
            </p>

            <p style="font-size:0.9rem; color:#666;">
                Para acessar sua conta, utilize seu CPF/CNPJ e o código de verificação enviado para seu e-mail.
            </p>
        </div>

        <div class="footer">
            <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
        </div>
    </div>
</body>
</html>
