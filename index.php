<?php
require_once 'includes/config.php';
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php if (TURNSTILE_ENABLED): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad" async defer></script>
    <?php endif; ?>
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
        var TURNSTILE_ENABLED = <?php echo json_encode(TURNSTILE_ENABLED); ?>;
        var TURNSTILE_THEME = <?php echo json_encode(TURNSTILE_THEME); ?>;
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-file-invoice"></i> NFS-e
            <small>Nota Fiscal de Serviços</small>
        </div>
        <nav>
            <a href="index.php"><i class="fas fa-user-tie"></i> Página Inicial</a>
            <a href="/admin/index.php"><i class="fas fa-sign-in-alt"></i> Painel Administrativo</a>
            <a href="contador_login.php"><i class="fas fa-sign-in-alt"></i> Acesso do Contador</a>
            <a href="politica.php"><i class="fas fa-shield-alt"></i> Política de Privacidade</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>
    <center><img src="assets/img/nfse.png" alt="Sistema NFS-e" width="320" height="200"></center>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-lock"></i> Acesse sua Conta</h2>
            <?php if (isset($_SESSION['erro_login'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['erro_login']; unset($_SESSION['erro_login']); ?>
                </div>
            <?php endif; ?>
            <form action="auth.php" method="POST" id="loginForm">
                <div class="form-group">
                    <center><label for="cpf_cnpj"><i class="fas fa-id-card"></i> CPF ou CNPJ</label>
                    <input type="text" name="cpf_cnpj" id="cpf_cnpj" placeholder="Digite apenas números" required></center>
                </div>
               <center><?php if (TURNSTILE_ENABLED): ?>
                    <div class="form-group">
                        <div class="cf-turnstile" data-sitekey="<?= TURNSTILE_SITE_KEY ?>" data-theme="<?= TURNSTILE_THEME ?>"></div>
                    </div>
                <?php endif; ?></center>
                <button type="submit" class="btn btn-block"><i class="fas fa-paper-plane"></i> Enviar Código</button>
            </form>
            <p style="text-align:center; margin-top:20px; font-size:0.9rem; color:#888;">
                <i class="fas fa-info-circle"></i> Você receberá um código em seu e-mail de Cadastro.
            </p>
        </div>

        <div class="infobox">
            <h3><i class="fas fa-shield-alt"></i> Segurança e Conformidade</h3><br>
            <p>
                O <strong>Sistema NFS-e</strong> é uma solução confiável para guarda de notas fiscais de serviços eletrônicas.<br>
                Armazenamos tudo com criptografia e backup, garantindo a integridade e disponibilidade dos dados.<br><br>
            </p>
            <ul>
                <li><i class="fas fa-check-circle"></i> Atende à <strong>LGPD</strong></li>
                <li><i class="fas fa-check-circle"></i> Backup Automático</li>
                <li><i class="fas fa-check-circle"></i> Autenticação 2FA</li>
                <li><i class="fas fa-check-circle"></i> Auditoria Completa</li>
            </ul>
        </div>
    </div>

    <footer>
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados.
    </footer>

    <script src="assets/js/script.js"></script>

<!-- BANNER DE COOKIES - VERSÃO COM FALLBACK -->
<div id="cookieBanner" style="display:none; position:fixed; bottom:80px; right:20px; z-index:99999; max-width:380px; width:100%;">
    <div style="background:#0d2b45; color:#fff; border-radius:16px; padding:20px; box-shadow:0 20px 60px rgba(0,0,0,0.4); border:1px solid rgba(168,230,207,0.2); animation:slideUp 0.5s ease-out;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <i class="fas fa-cookie-bite" style="color:#a8e6cf; font-size:1.2rem;"></i>
            <span style="flex:1; font-weight:600; font-size:1rem; color:#fff;">Nós usamos Cookies</span>
            <button onclick="fecharCookie()" style="background:none; border:none; color:rgba(255,255,255,0.5); font-size:1.4rem; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <p style="margin:0 0 16px 0; font-size:0.92rem; line-height:1.6; color:#e0edf5;">
            Utilizamos cookies para melhorar sua experiência, garantir segurança e cumprir a <strong>LGPD</strong>. 
            <a href="politica.php" target="_self" style="color:#a8e6cf; font-weight:600; text-decoration:underline;">Saiba mais</a>
        </p>
        <div style="display:flex; gap:10px;">
            <!-- Fallback inline: chama a função global e, se falhar, usa window.aceitarCookies -->
            <button onclick="if(typeof aceitarCookies === 'function'){aceitarCookies();}else{console.error('aceitarCookies não definida');}" style="flex:1; background:#a8e6cf; color:#0d2b45; border:none; padding:10px 20px; border-radius:40px; font-weight:700; font-size:0.9rem; cursor:pointer; transition:all 0.3s;">
                <i class="fas fa-check"></i> Aceitar
            </button>
            <button onclick="fecharCookie()" style="flex:1; background:rgba(255,255,255,0.08); color:#fff; border:1px solid rgba(255,255,255,0.15); padding:10px 20px; border-radius:40px; font-weight:600; font-size:0.9rem; cursor:pointer; transition:all 0.3s;">
                <i class="fas fa-times"></i> Recusar
            </button>
        </div>
    </div>
</div>

<script>
    // Garante que as funções estejam disponíveis mesmo se o script.js falhar
    if (typeof aceitarCookies !== 'function') {
        window.aceitarCookies = function() {
            localStorage.setItem('cookies_aceitos', 'sim');
            document.getElementById('cookieBanner').style.display = 'none';
        };
        window.fecharCookie = function() {
            document.getElementById('cookieBanner').style.display = 'none';
        };
        // Tenta exibir o banner se ainda não foi aceito
        if (localStorage.getItem('cookies_aceitos') !== 'sim') {
            var banner = document.getElementById('cookieBanner');
            if (banner) banner.style.display = 'block';
        }
    }
</script>

</body>
</html>
