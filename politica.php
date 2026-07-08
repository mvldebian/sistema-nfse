<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e - Privacidade</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var FORCAR_TEMA_CLARO = <?php echo json_encode(FORCAR_TEMA_CLARO); ?>;
    </script>
    <style>
        /* Animações e interações */
        .politica-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .politica-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px 45px;
            box-shadow: 0 12px 40px rgba(0, 20, 40, 0.06);
            border: 1px solid #eef3f8;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
        }
        body.tema-escuro .politica-card {
            background: #1a1f26;
            border-color: #2a3038;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .politica-card h1 {
            color: #0d2b45;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        body.tema-escuro .politica-card h1 {
            color: #90caf9;
        }
        .politica-card .subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e6edf4;
        }
        body.tema-escuro .politica-card .subtitle {
            color: #aaa;
            border-bottom-color: #2a3038;
        }
        .politica-card h2 {
            color: #0d2b45;
            font-size: 1.3rem;
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        body.tema-escuro .politica-card h2 {
            color: #90caf9;
        }
        .politica-card h2 i {
            color: #0d47a1;
            font-size: 1.2rem;
        }
        body.tema-escuro .politica-card h2 i {
            color: #64b5f6;
        }
        .politica-card p {
            color: #1a2a3a;
            line-height: 1.8;
            margin-bottom: 14px;
        }
        body.tema-escuro .politica-card p {
            color: #dde7f0;
        }
        .politica-card ul {
            list-style: none;
            padding: 0;
            margin: 10px 0 20px 0;
        }
        .politica-card ul li {
            padding: 8px 0 8px 32px;
            position: relative;
            color: #1a2a3a;
            line-height: 1.6;
        }
        body.tema-escuro .politica-card ul li {
            color: #dde7f0;
        }
        .politica-card ul li i {
            position: absolute;
            left: 0;
            top: 10px;
            color: #0d47a1;
        }
        body.tema-escuro .politica-card ul li i {
            color: #64b5f6;
        }
        .politica-card .highlight-box {
            background: #e3f0ff;
            border-radius: 16px;
            padding: 20px 24px;
            margin: 20px 0;
            border-left: 5px solid #0d47a1;
        }
        body.tema-escuro .politica-card .highlight-box {
            background: #1a2a3a;
            border-left-color: #64b5f6;
        }
        .politica-card .highlight-box p {
            margin: 0;
            font-weight: 500;
        }
        .politica-card .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0d2b45;
            color: #fff;
            padding: 12px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 25px;
        }
        .politica-card .btn-voltar:hover {
            background: #1a3a5c;
            transform: translateX(-4px);
            box-shadow: 0 6px 20px rgba(13, 43, 69, 0.2);
        }
        body.tema-escuro .politica-card .btn-voltar {
            background: #1a1f26;
            color: #fff;
        }
        body.tema-escuro .politica-card .btn-voltar:hover {
            background: #2a3038;
        }
        /* Tabs interativas */
        .tab-container {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 20px 0 10px 0;
        }
        .tab-btn {
            padding: 8px 20px;
            background: #eef3f8;
            border: none;
            border-radius: 30px;
            font-weight: 500;
            color: #1a2a3a;
            cursor: pointer;
            transition: all 0.25s;
            font-size: 0.9rem;
        }
        .tab-btn:hover {
            background: #dde7f0;
        }
        .tab-btn.active {
            background: #0d2b45;
            color: #fff;
        }
        body.tema-escuro .tab-btn {
            background: #2a3038;
            color: #dde7f0;
        }
        body.tema-escuro .tab-btn:hover {
            background: #3a434e;
        }
        body.tema-escuro .tab-btn.active {
            background: #90caf9;
            color: #0d2b45;
        }
        .tab-content {
            display: none;
            animation: fadeInUp 0.4s ease;
        }
        .tab-content.active {
            display: block;
        }
        /* Scroll suave */
        html {
            scroll-behavior: smooth;
        }
        /* Responsivo */
        @media (max-width: 768px) {
            .politica-card {
                padding: 24px 18px;
            }
            .politica-card h1 {
                font-size: 1.6rem;
            }
            .politica-card h2 {
                font-size: 1.1rem;
            }
            .tab-container {
                gap: 6px;
            }
            .tab-btn {
                padding: 6px 14px;
                font-size: 0.8rem;
            }
            .politica-card ul li {
                padding-left: 28px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-file-invoice"></i> NFS-e
            <small>Nota Fiscal de Serviços</small>
        </div>
        <nav>
            <a href="index.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <button id="btnTema" class="btn-tema"><i class="fas fa-sun"></i></button>
        </nav>
    </header>

    <div class="container">
        <div class="politica-wrapper">
            <div class="politica-card">
                <h1><i class="fas fa-shield-alt" style="color:#0d47a1;"></i> Política de Privacidade</h1>
                <div class="subtitle">
                    <i class="fas fa-calendar-alt"></i> Última Atualização: 07/07/2026 &bull; 
                    <i class="fas fa-gavel"></i> Conformidade com a LGPD (Lei 13.709/2018)
                </div>

                <!-- Tabs interativas -->
                <div class="tab-container">
                    <button class="tab-btn active" data-tab="tab1"><i class="fas fa-info-circle"></i> Visão Geral</button>
                    <button class="tab-btn" data-tab="tab2"><i class="fas fa-database"></i> Dados Coletados</button>
                    <button class="tab-btn" data-tab="tab3"><i class="fas fa-cookie-bite"></i> Cookies</button>
                    <button class="tab-btn" data-tab="tab4"><i class="fas fa-user-shield"></i> Seus Direitos</button>
                </div>

                <!-- Conteúdo das Tabs -->
                <div id="tab1" class="tab-content active">
                    <br><p>O <strong>Sistema NFS-e</strong> armazena e gerencia notas fiscais de serviços.
                    <br>Protegemos seus dados pessoais em conformidade com a <strong>Lei Geral de Proteção de Dados (LGPD)</strong>.</p>
                    
                    <div class="highlight-box">
                        <p><i class="fas fa-check-circle" style="color:#2e7d32;"></i> Seus dados são usados Exclusivamente:</p>
                        <ul style="margin:8px 0 0 0;">
                            <li style="padding:2px 0 2px 28px;"><i class="fas fa-check" style="position:relative; top:0; font-size:0.9rem;"></i> Autenticação segura no Sistema</li>
                            <li style="padding:2px 0 2px 28px;"><i class="fas fa-check" style="position:relative; top:0; font-size:0.9rem;"></i> Armazenamento de notas Fiscais</li>
                            <li style="padding:2px 0 2px 28px;"><i class="fas fa-check" style="position:relative; top:0; font-size:0.9rem;"></i> Envio de notificações e Comunicados</li>
                        </ul>
                    </div>
                </div>

                <div id="tab2" class="tab-content">
                    <h2><i class="fas fa-user"></i> Dados Pessoais</h2>
                    <ul>
                        <li><i class="fas fa-id-card"></i> <strong>Nome e Razão Social</strong> – identificação do Usuário</li>
                        <li><i class="fas fa-envelope"></i> <strong>E-mail</strong> – para login, recuperação de senha e Notificações</li>
                        <li><i class="fas fa-address-card"></i> <strong>CPF/CNPJ</strong> – identificação fiscal e organização das Notas Fiscais</li>
                        <li><i class="fas fa-hdd"></i> <strong>Arquivos</strong> – notas fiscais em XML e PDF</li>
                    </ul>
                    <p style="margin-top:10px;"><i class="fas fa-lock" style="color:#0d47a1;"></i> Todos os dados são armazenados com criptografia e Acesso Restrito.</p>
                </div>

                <div id="tab3" class="tab-content">
                    <h2><i class="fas fa-cookie-bite"></i> Uso de Cookies</h2>
                    <p>Este sistema utiliza cookies para Garantir:</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> <strong>Autenticação</strong> – mantém sua sessão Ativa</li>
                        <li><i class="fas fa-check-circle"></i> <strong>Preferências</strong> – lembra sua escolha de tema (Claro/Escuro)</li>
                        <li><i class="fas fa-check-circle"></i> <strong>Segurança</strong> – protege contra acessos não Autorizados</li>
                    </ul>
                    <p style="margin-top:10px;">Não utilizamos nenhum tipo de cookies de rastreamento ou publicidade.</p>
                </div>

                <div id="tab4" class="tab-content">
                    <h2><i class="fas fa-user-shield"></i> Seus Direitos (LGPD)</h2>
                    <ul>
                        <li><i class="fas fa-check"></i> <strong>Confirmação</strong> – saber se seus dados são Tratados</li>
                        <li><i class="fas fa-check"></i> <strong>Acesso</strong> – visualizar todos os seus Dados</li>
                        <li><i class="fas fa-check"></i> <strong>Correção</strong> – solicitar alteração de dados Incompletos</li>
                        <li><i class="fas fa-check"></i> <strong>Exclusão</strong> – solicitar remoção de seus Dados</li>
                        <li><i class="fas fa-check"></i> <strong>Portabilidade</strong> – receber seus dados em formato Estruturado</li>
                    </ul>
                    <div class="highlight-box">
                        <p><i class="fas fa-envelope"></i> Para exercer seus direitos, entre em contato: <strong>lgpd@eth1.com.br</strong></p>
                    </div>
                </div>

                <!-- Rodapé da página -->
                <div style="margin-top:35px; padding-top:25px; border-top:2px solid #e6edf4;">
                    <p style="font-size:0.9rem; color:#888;">
                        <i class="fas fa-lock" style="color:#0d47a1;"></i> 
                        Sistema em conformidade com a <strong>LGPD</strong> (Lei 13.709/2018) e garante a segurança e privacidade dos seus dados.
                    </p>
                    <a href="javascript:history.back()" class="btn-voltar">
                        <i class="fas fa-arrow-left"></i> Página Incial
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <i class="fas fa-file-invoice"></i> Sistema NFS-e - Guarda de Notas Fiscais de Serviço &bull; &copy; <?= date('Y') ?> Todos os Direitos Reservados. 
    </footer>

    <script src="assets/js/script.js"></script>
    <script>
        // Controle de Tabs
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active de todos
                    tabs.forEach(b => b.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));

                    // Ativa o clicado
                    this.classList.add('active');
                    const target = document.getElementById(this.dataset.tab);
                    if (target) target.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
