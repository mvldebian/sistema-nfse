<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logado'])) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$erro = '';
$teste_enviado = false;

// Busca todos os emails (usuários + contadores)
$stmt = $pdo->query("SELECT email FROM usuarios WHERE is_admin = 0");
$usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT email FROM contadores WHERE ativo = 1");
$contadores = $stmt->fetchAll(PDO::FETCH_COLUMN);

$todos_emails = array_merge($usuarios, $contadores);
$total_destinatarios = count($todos_emails);

// Processa envio
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'enviar_todos') {
        $assunto = trim($_POST['assunto'] ?? '');
        $conteudo = $_POST['conteudo'] ?? '';

        if (empty($assunto) || empty($conteudo)) {
            $erro = 'Preencha assunto e conteúdo.';
        } else {
            // Conteúdo completo com cabeçalho e rodapé
            $template_cabecalho = file_get_contents(__DIR__ . '/../templates/email_cabecalho.html');
            $template_rodape = file_get_contents(__DIR__ . '/../templates/email_rodape.html');

            $corpo_completo = $template_cabecalho . $conteudo . $template_rodape;

            $enviados = 0;
            $falhas = 0;

            foreach ($todos_emails as $email) {
                if (empty($email)) continue;
                $enviado = enviar_email_raw($email, $assunto, $corpo_completo);
                if ($enviado) {
                    $enviados++;
                } else {
                    $falhas++;
                }
                usleep(50000);
            }

            $mensagem = "Notificação enviada para $enviados destinatários. Falhas: $falhas.";
        }
    } elseif ($action === 'enviar_teste') {
        $email_teste = trim($_POST['email_teste'] ?? '');
        $assunto_teste = trim($_POST['assunto_teste'] ?? '');
        $conteudo_teste = $_POST['conteudo_teste'] ?? '';

        if (empty($email_teste) || !filter_var($email_teste, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail de teste inválido.';
        } elseif (empty($assunto_teste) || empty($conteudo_teste)) {
            $erro = 'Preencha assunto e conteúdo do teste.';
        } else {
            $template_cabecalho = file_get_contents(__DIR__ . '/../templates/email_cabecalho.html');
            $template_rodape = file_get_contents(__DIR__ . '/../templates/email_rodape.html');
            $corpo_completo = $template_cabecalho . $conteudo_teste . $template_rodape;

            $enviado = enviar_email_raw($email_teste, $assunto_teste, $corpo_completo);
            if ($enviado) {
                $mensagem = "E-mail de teste enviado para $email_teste com sucesso!";
                $teste_enviado = true;
            } else {
                $erro = 'Erro ao enviar e-mail de teste. Verifique as configurações de SMTP.';
            }
        }
    }
}

// Função auxiliar para enviar e-mail com corpo HTML completo
function enviar_email_raw($destino, $assunto, $corpo) {
    if (!defined('MAIL_HOST') || !defined('MAIL_USERNAME')) {
        error_log("Constantes de e-mail não definidas em config.php");
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
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
        $mail->Body = $corpo;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e Admin - Informativos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }
        body.tema-escuro .modal-box {
            background: #1a1f26;
            color: #dde7f0;
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.8rem;
            cursor: pointer;
            color: #888;
            background: none;
            border: none;
        }
        .modal-close:hover {
            color: #333;
        }
        body.tema-escuro .modal-close:hover {
            color: #fff;
        }
        .btn-modal {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 40px;
            background: #0d2b45;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-modal:hover {
            background: #1a3a5c;
        }
        .btn-modal-secondary {
            background: #e3edf7;
            color: #0d2b45;
            margin-top: 10px;
        }
        .btn-modal-secondary:hover {
            background: #d0dce8;
        }
        body.tema-escuro .btn-modal {
            background: #1a1f26;
            color: #fff;
        }
        body.tema-escuro .btn-modal:hover {
            background: #2a3038;
        }
        body.tema-escuro .btn-modal-secondary {
            background: #2a3038;
            color: #e0edf5;
        }
        body.tema-escuro .btn-modal-secondary:hover {
            background: #3a434e;
        }
        .note-editor {
            border-radius: 12px !important;
            border-color: #dde7f0 !important;
        }
        body.tema-escuro .note-editor {
            border-color: #3a434e !important;
            background: #1a1f26;
        }
        body.tema-escuro .note-editor .note-toolbar {
            background: #242b33;
            border-bottom-color: #3a434e;
        }
        body.tema-escuro .note-editor .note-toolbar .note-btn {
            background: #2a3038;
            color: #dde7f0;
        }
        body.tema-escuro .note-editor .note-toolbar .note-btn:hover {
            background: #3a434e;
        }
        body.tema-escuro .note-editor .note-editing-area .note-editable {
            background: #1a1f26;
            color: #dde7f0;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-user-cog"></i> Painel Administrativo</div>
        <nav>
            <a href="dashboard.php"><i class="fas fa-home"></i> Início</a>
            <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
            <a href="create_user.php"><i class="fas fa-user-plus"></i> Usuários</a>
            <a href="browse.php"><i class="fas fa-folder-open"></i> Pastas</a>
            <a href="agendamento.php"><i class="fas fa-calendar-alt"></i> Agendamento</a>
            <a href="informativos.php"><i class="fas fa-bullhorn"></i> Informativos</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width: 900px;">
            <h2><i class="fas fa-bullhorn"></i> Enviar Informativo</h2>

            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div style="background:#e3edf7; border-radius:12px; padding:15px 20px; margin-bottom:20px;">
                <p><i class="fas fa-users"></i> <strong><?= $total_destinatarios ?></strong> destinatários (usuários + contadores ativos).</p>
                <p style="font-size:0.9rem; color:#555;">O e-mail será enviado com cabeçalho e rodapé institucionais pré definidos em Templates.</p>
            </div>

            <form method="POST" id="formEnvio">
                <input type="hidden" name="action" value="enviar_todos">
                <div class="form-group">
                    <label for="assunto"><i class="fas fa-tag"></i> Assunto</label>
                    <input type="text" name="assunto" id="assunto" class="form-control" placeholder="Digite o assunto do e-mail" required style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #dde7f0; font-size:1rem;">
                </div>
                <div class="form-group">
                    <label for="conteudo"><i class="fas fa-edit"></i> Conteúdo (HTML) para nova linha sem espaçamento utilize SHIFT+ENTER</label>
                    <textarea name="conteudo" id="conteudo" class="form-control" rows="10" style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #dde7f0; font-size:1rem;"></textarea>
                </div>
                <button type="submit" class="btn btn-block" onclick="return confirm('Enviar para todos os <?= $total_destinatarios ?> destinatários?')">
                    <i class="fas fa-paper-plane"></i> Enviar para Todos
                </button>
            </form>

            <hr style="margin:30px 0; border-color:#eee;">

            <div style="text-align:center;">
                <button class="btn btn-secondary" onclick="abrirModal('modalTeste')" style="background:#e65100; color:#fff; padding:12px 30px; border:none; border-radius:40px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-vial"></i> Enviar Teste
                </button>
            </div>
        </div>
    </div>

    <!-- ===== MODAL TESTE ===== -->
    <div id="modalTeste" class="modal-overlay">
        <div class="modal-box" style="max-width: 800px;">
            <button class="modal-close" onclick="fecharModal('modalTeste')">&times;</button>
            <h3><i class="fas fa-vial"></i> Envio de Teste</h3>
            <br><p style="color:#666; margin-bottom:20px;">Versão de teste do informativo para um único E-mail.</p>
            <form method="POST" id="formTeste">
                <input type="hidden" name="action" value="enviar_teste">
                <div class="form-group">
                    <label for="email_teste"><i class="fas fa-envelope"></i> E-mail de Teste</label>
                    <input type="email" name="email_teste" id="email_teste" class="form-control" placeholder="pessoa@exemplo.com.br" required style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #dde7f0; font-size:1rem;">
                </div>
                <div class="form-group">
                    <label for="assunto_teste"><i class="fas fa-tag"></i> Assunto</label>
                    <input type="text" name="assunto_teste" id="assunto_teste" class="form-control" placeholder="Assunto do teste" required style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #dde7f0; font-size:1rem;">
                </div>
                <div class="form-group">
                    <label for="conteudo_teste"><i class="fas fa-edit"></i> Conteúdo (HTML) para nova linha sem espaçamento utilize SHIFT+ENTER</label>
                    <textarea name="conteudo_teste" id="conteudo_teste" class="form-control" rows="8" style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #dde7f0; font-size:1rem;"></textarea>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-modal"><i class="fas fa-paper-plane"></i> Enviar Teste</button>
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="fecharModal('modalTeste')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <i class="fas fa-bullhorn"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script>
        // ===== SUMMERNOTE =====
        $(document).ready(function() {
            $('#conteudo').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        // Upload de imagem (opcional)
                        // Pode implementar upload para servidor, mas por simplicidade usamos base64
                        for (let i = 0; i < files.length; i++) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                $('#conteudo').summernote('insertImage', e.target.result);
                            };
                            reader.readAsDataURL(files[i]);
                        }
                    }
                }
            });

            // Inicializa o editor no modal de teste
            $('#conteudo_teste').summernote({
                height: 200,
                toolbar: [
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture']],
                    ['view', ['codeview']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        for (let i = 0; i < files.length; i++) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                $('#conteudo_teste').summernote('insertImage', e.target.result);
                            };
                            reader.readAsDataURL(files[i]);
                        }
                    }
                }
            });
        });

        // ===== MODAIS =====
        function abrirModal(id) {
            document.getElementById(id).classList.add('active');
            // Inicializa o summernote se não estiver ativo
            if (id === 'modalTeste') {
                setTimeout(function() {
                    $('#conteudo_teste').summernote('reset');
                }, 100);
            }
        }
        function fecharModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        // Fechar ao clicar fora
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Sincroniza o conteúdo do Summernote antes de enviar o formulário
        document.getElementById('formEnvio').addEventListener('submit', function(e) {
            var conteudo = $('#conteudo').summernote('code');
            document.querySelector('textarea[name="conteudo"]').value = conteudo;
        });
        document.getElementById('formTeste').addEventListener('submit', function(e) {
            var conteudo = $('#conteudo_teste').summernote('code');
            document.querySelector('textarea[name="conteudo_teste"]').value = conteudo;
        });
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>
