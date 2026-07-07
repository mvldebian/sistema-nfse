<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['contador_id'])) {
    header('Location: contador_login.php');
    exit;
}

$pasta = $_SESSION['contador_pasta'];
$usuario_nome = $_SESSION['contador_usuario_nome'];
$caminho_base = UPLOAD_DIR . $pasta;

if (!is_dir($caminho_base)) {
    mkdir($caminho_base, 0755, true);
}

// ===== MÉTRICAS =====
$total_xml = contar_arquivos_por_extensao($pasta, 'xml');
$total_pdf = contar_arquivos_por_extensao($pasta, 'pdf');
$tamanho_usado = calcular_tamanho_pasta($caminho_base);
$quota = QUOTA_BYTES;
$percentual = ($tamanho_usado / $quota) * 100;
if ($percentual > 100) $percentual = 100;

// ===== NAVEGAÇÃO =====
$diretorio_atual = isset($_GET['dir']) ? trim($_GET['dir']) : '';
$caminho_completo = realpath($caminho_base . DIRECTORY_SEPARATOR . $diretorio_atual);
if ($caminho_completo === false || strpos($caminho_completo, realpath($caminho_base)) !== 0) {
    $caminho_completo = $caminho_base;
    $diretorio_atual = '';
}

$itens = scandir($caminho_completo);
$itens = array_diff($itens, array('.', '..'));

$pastas = [];
$arquivos = [];
foreach ($itens as $item) {
    if (is_dir($caminho_completo . DIRECTORY_SEPARATOR . $item)) {
        $pastas[] = $item;
    } else {
        $arquivos[] = $item;
    }
}
sort($pastas);
sort($arquivos);
$itens_ordenados = array_merge($pastas, $arquivos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Dashboard Contador</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
    <style>
        /* CSS INLINE PARA GARANTIR O LAYOUT */
        .metricas-wrapper {
            width: 100%;
            margin-bottom: 35px;
        }
        .metricas-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card-metrica {
            border-radius: 24px;
            padding: 28px 24px 22px;
            display: flex;
            flex-direction: column;
            min-height: 160px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .card-metrica:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
        }
        .card-metrica .card-conteudo {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex: 1;
        }
        .card-metrica .info {
            flex: 1;
        }
        .card-metrica .info .valor {
            font-size: 2.4rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 4px;
        }
        .card-metrica .info .rotulo {
            font-size: 1rem;
            font-weight: 500;
        }
        .card-metrica .info .variacao {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 8px;
        }
        .card-metrica .info .variacao i {
            font-size: 0.9rem;
        }
        .card-metrica .badge {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 30px;
            padding: 2px 12px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 4px;
            backdrop-filter: blur(4px);
        }
        .card-metrica .icone-grande {
            font-size: 3.2rem;
            flex-shrink: 0;
            margin-left: 12px;
            line-height: 1;
            opacity: 0.3;
        }

        /* Cores */
        .card-metrica.azul {
            background: linear-gradient(135deg, #1a3a5c, #0d47a1);
            color: #ffffff;
        }
        .card-metrica.verde {
            background: linear-gradient(135deg, #1b5e20, #2e7d32);
            color: #ffffff;
        }
        .card-metrica.laranja {
            background: linear-gradient(135deg, #bf360c, #e65100);
            color: #ffffff;
        }
        .card-metrica.roxo {
            background: linear-gradient(135deg, #4a148c, #6a1b9a);
            color: #ffffff;
        }
        .card-metrica .variacao.positivo {
            color: #a5d6a7;
        }
        .card-metrica .variacao.negativo {
            color: #ef9a9a;
        }

        /* Responsivo */
        @media (max-width: 1024px) {
            .metricas-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
            }
        }
        @media (max-width: 600px) {
            .metricas-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .card-metrica {
                padding: 22px 20px 18px;
                min-height: 130px;
            }
            .card-metrica .info .valor {
                font-size: 2rem;
            }
            .card-metrica .icone-grande {
                font-size: 2.6rem;
            }
        }

        body.tema-escuro .card-metrica.azul {
            background: linear-gradient(135deg, #0a1a2a, #0d2b45);
        }
        body.tema-escuro .card-metrica.verde {
            background: linear-gradient(135deg, #0a1a0a, #1b3a1b);
        }
        body.tema-escuro .card-metrica.laranja {
            background: linear-gradient(135deg, #2a1a0a, #3a1a0a);
        }
        body.tema-escuro .card-metrica.roxo {
            background: linear-gradient(135deg, #1a0a2a, #2a0a3a);
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-file-invoice"></i> NFS-e
            <small>Painel do Contador</small>
        </div>
        <nav>
            <span><i class="fas fa-user"></i> Seja Bem-vindo Contador!</span>
            <a href="contador_dashboard.php"><i class="fas fa-home"></i> Início</a>
            <a href="logout_contador.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
      <!-- MÉTRICAS - CARDS GRANDES E COLORIDOS -->
<div class="metricas-wrapper">
    <div class="metricas-grid">
        <!-- XMLs - AZUL -->
        <div class="card-metrica azul">
            <div class="card-conteudo">
                <div class="info">
                    <div class="valor"><?= $total_xml ?></div>
                    <div class="rotulo">XMLs</div>
                    <div class="variacao positivo">
                        <i class="fas fa-arrow-up"></i> <span class="badge">Total de Arquivos</span>
                    </div>
                </div>
                <div class="icone-grande"><i class="fas fa-file-code"></i></div>
            </div>
        </div>

        <!-- DANFes - VERDE -->
        <div class="card-metrica verde">
            <div class="card-conteudo">
                <div class="info">
                    <div class="valor"><?= $total_pdf ?></div>
                    <div class="rotulo">DANFes</div>
                    <div class="variacao positivo">
                        <i class="fas fa-arrow-up"></i> <span class="badge">Total de Arquivos</span>
                    </div>
                </div>
                <div class="icone-grande"><i class="fas fa-file-pdf"></i></div>
            </div>
        </div>

        <!-- Uso de Disco - LARANJA -->
        <div class="card-metrica laranja">
            <div class="card-conteudo">
                <div class="info">
                    <div class="valor"><?= formatar_tamanho($tamanho_usado) ?></div>
                    <div class="rotulo">Uso de Disco</div>
                    <div class="variacao <?= ($tamanho_usado > $quota * 0.8) ? 'negativo' : 'positivo' ?>">
                        <?php if ($tamanho_usado > $quota * 0.8): ?>
                            <i class="fas fa-arrow-up"></i> <?= number_format($percentual, 1) ?>% da quota
                        <?php else: ?>
                            <i class="fas fa-arrow-down"></i> <?= number_format($percentual, 1) ?>% da quota
                        <?php endif; ?>
                    </div>
                </div>
                <div class="icone-grande"><i class="fas fa-hdd"></i></div>
            </div>
        </div>

        <!-- Ocupação - ROXO -->
        <div class="card-metrica roxo">
            <div class="card-conteudo">
                <div class="info">
                    <div class="valor"><?= number_format($percentual, 1) ?>%</div>
                    <div class="rotulo">Quota 1GB</div>
                    <div class="variacao <?= ($percentual > 90) ? 'negativo' : 'positivo' ?>">
                        <?php if ($percentual > 90): ?>
                            <i class="fas fa-exclamation-triangle"></i> Quota Crítica
                        <?php else: ?>
                            <i class="fas fa-check-circle"></i> Quota OK
                        <?php endif; ?>
                    </div>
                </div>
                <div class="icone-grande"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>
    </div>
</div>

        <!-- LISTA DE ARQUIVOS -->
        <div class="lista-arquivos">
            <h2><i class="fas fa-folder"></i> Notas fiscais de <?= htmlspecialchars($usuario_nome) ?></h2>
            <div style="margin-bottom:20px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span style="color:#aaa;">Pasta atual:</span>
                <span style="background:#000; color:#fff; padding:5px 15px; border-radius:20px; font-weight:bold;">
                    <?= $diretorio_atual ? htmlspecialchars($diretorio_atual) : 'Raiz' ?>
                </span>
                <?php if ($diretorio_atual): ?>
                    <a href="?dir=<?= urlencode(dirname($diretorio_atual)) ?>" class="btn" style="padding:5px 15px; font-size:0.9rem;">
                        <i class="fas fa-arrow-up"></i> Subir
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($itens_ordenados)): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> Esta pasta está Vazia.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tamanho</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens_ordenados as $item): ?>
                            <?php
                            $caminho_item = $caminho_completo . DIRECTORY_SEPARATOR . $item;
                            $is_dir = is_dir($caminho_item);
                            $icone = $is_dir ? 'fa-folder' : 'fa-file';
                            
                            if ($is_dir) {
                                $tamanho = formatar_tamanho(calcular_tamanho_pasta($caminho_item));
                                $link = "?dir=" . urlencode($diretorio_atual ? $diretorio_atual . '/' . $item : $item);
                                $acao = '<span style="color:#888;">📂 Pasta</span>';
                            } else {
                                $tamanho = formatar_tamanho(filesize($caminho_item));
                                $link = "download.php?file=" . urlencode($diretorio_atual ? $diretorio_atual . '/' . $item : $item);
                                $acao = '<a href="' . $link . '" class="btn" style="padding:3px 12px; font-size:0.8rem;"><i class="fas fa-download"></i> Baixar</a>';
                            }
                            ?>
                            <tr>
                                <td>
                                    <i class="fas <?= $icone ?> icone"></i>
                                    <?php if ($is_dir): ?>
                                        <a href="<?= $link ?>"><?= htmlspecialchars($item) ?></a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($item) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $tamanho ?></td>
                                <td><?= $acao ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
