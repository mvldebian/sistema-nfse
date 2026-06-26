<?php
// Configura o locale para português (se disponível)
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

// Array manual de meses em português (fallback caso o locale não funcione)
$meses = [
    'January' => 'Janeiro',
    'February' => 'Fevereiro',
    'March' => 'Março',
    'April' => 'Abril',
    'May' => 'Maio',
    'June' => 'Junho',
    'July' => 'Julho',
    'August' => 'Agosto',
    'September' => 'Setembro',
    'October' => 'Outubro',
    'November' => 'Novembro',
    'December' => 'Dezembro'
];

// Obtém o mês atual em português
$mes_atual = $meses[date('F')] ?? date('F');
$ano_atual = date('Y');
$mes_ano = $mes_atual . ' de ' . $ano_atual;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NFS-e - Notificação Mensal</title>
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

            <p>Informamos que as notas do mês <strong><?= $mes_ano ?></strong> estão disponíveis no sistema <strong>NFS-e</strong>.</p>

            <div class="destaque">
                <i class="fas fa-check-circle" style="color:#2e7d32;"></i> 
                Acesse agora mesmo e visualize seus documentos.
            </div>

            <p style="text-align:center;">
                <a href="<?= BASE_URL ?>" class="btn"><i class="fas fa-arrow-right"></i> Acessar sistema</a>
            </p>

            <center><p style="font-size:0.9rem; color:#666;">
                Este é um e-mail automático. Caso não tenha solicitado, ignore esta mensagem.
            </p></center>
        </div>

        <div class="footer">
            <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
        </div>
    </div>
</body>
</html>
