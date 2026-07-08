<?php
require_once 'includes/config.php';
if (isset($_SESSION['contador_id'])) {
    header('Location: contador_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Acesso Contabilidade</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php if (TURNSTILE_ENABLED): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad" async defer></script>
    <?php endif; ?>
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
        var TURNSTILE_ENABLED = <?php echo json_encode(TURNSTILE_ENABLED); ?>;
        var TURNSTILE_SITE_KEY = <?php echo json_encode(TURNSTILE_SITE_KEY); ?>;
        var TURNSTILE_THEME = <?php echo json_encode(TURNSTILE_THEME); ?>;

        // Função chamada quando a API carrega
        function onTurnstileLoad() {
            console.log('Turnstile API carregada');
            // Se o widget não foi renderizado automaticamente, renderiza manualmente
            var container = document.querySelector('.cf-turnstile');
            if (container && !container.querySelector('iframe')) {
                try {
                    turnstile.render(container, {
                        sitekey: TURNSTILE_SITE_KEY,
                        theme: TURNSTILE_THEME,
                        callback: function(token) {
                            console.log('Turnstile validado com Sucesso');
                        }
                    });
                } catch (e) {
                    console.error('Erro ao renderizar Turnstile:', e);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (TURNSTILE_ENABLED) {
                // Se após 5 segundos o widget não aparecer, tenta renderizar manualmente
                setTimeout(function() {
                    var container = document.querySelector('.cf-turnstile');
                    if (container && !container.querySelector('iframe')) {
                        console.warn('Turnstile não carregou automaticamente - renderizando manualmente');
                        try {
                            // Tenta renderizar usando a função global turnstile
                            if (typeof turnstile !== 'undefined') {
                                turnstile.render(container, {
                                    sitekey: TURNSTILE_SITE_KEY,
                                    theme: TURNSTILE_THEME
                                });
                            } else {
                                // Se a API não estiver disponível, mostra fallback com recarregar
                                container.innerHTML = '<div style="padding:12px; background:#fff3cd; border-radius:8px; color:#856404; text-align:center; border:1px solid #ffc107;">' +
                                    '<i class="fas fa-exclamation-triangle"></i> Verificação de segurança não carregou. ' +
                                    '<button onclick="location.reload()" style="background:#ffc107; border:none; padding:6px 18px; border-radius:20px; cursor:pointer; font-weight:600; margin-left:8px;">' +
                                    'Recarregar página</button>' +
                                    '</div>';
                            }
                        } catch (e) {
                            console.error('Erro ao renderizar manualmente:', e);
                        }
                    }
                }, 3000);
            }
        });
    </script>
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-file-invoice"></i> NFS-e
        <small>Nota Fiscal de Serviços</small>
        </div>
        <nav>
            <a href="index.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>
    <center><img src="assets/img/nfse.png" alt="Sistema NFS-e" width="320" height="200"></center>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-user-tie"></i> Acesso para Contabilidade</h2>
            <?php if (isset($_SESSION['erro_contador'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['erro_contador']; unset($_SESSION['erro_contador']); ?></div>
            <?php endif; ?>
            <form action="auth_contador.php" method="POST">
                <div class="form-group">
                    <center><label for="email"><i class="fas fa-envelope"></i> Informe o E-mail </label>
                    <input type="email" name="email" id="email" placeholder="fulano@exemplocontabil.com.br" required></center>
                </div>
                <center><?php if (TURNSTILE_ENABLED): ?>
                    <div class="form-group">
                        <div class="cf-turnstile"></div>
                    </div>
                <?php endif; ?></center>
                <button type="submit" class="btn btn-block"><i class="fas fa-paper-plane"></i> Enviar Código</button>
            </form>
             <p style="text-align:center; margin-top:20px; font-size:0.9rem; color:#888;">
                <i class="fas fa-info-circle"></i> Você receberá um código em seu e-mail de Cadastro.
            </p>
            <p style="text-align:center; margin-top:20px;">
                <a href="index.php" style="color:#0d2b45;">Página Inicial</a>
            </p>
        </div>
    </div>

    <footer>
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
