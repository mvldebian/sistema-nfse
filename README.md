# sistema-nfse

<img width="1277" height="931" alt="image" src="https://github.com/user-attachments/assets/1e46f995-d4e6-4f9e-a2ac-ff152baab1c1" />


O Sistema NFS-e é uma plataforma web desenvolvida para guarda, gestão e distribuição de notas fiscais de serviços eletrônicas, oferecendo segurança, conformidade e facilidade de uso para contribuintes e administradores. Este sistema é ideal para empresas que emitem notas fiscais de serviços e desejam um repositório centralizado, seguro e de fácil acesso para seus documentos fiscais.

🔐 Autenticação e Segurança

Login com CPF/CNPJ: usuários acessam utilizando seu documento cadastrado.

Autenticação por Token com envio de código de verificação por e-mail (PHPMailer com SMTP).

Proteção contra bots: integração com Cloudflare Turnstile (opcional, com configuração de estilo via config.php).

Sessão segura: gerenciamento de sessão com hash de senha (bcrypt).

👤 Perfil do Usuário

Pasta exclusiva por usuário: cada usuário possui uma subpasta dentro de uploads/ nomeada pelo CPF/CNPJ.

Download de arquivos: download individual ou da pasta inteira compactada em ZIP.

Métricas personalizadas: dashboard com contagem de XMLs, DANFes (PDFs) e uso de disco com quota de 1 GB

🛠️ Painel Administrativo

Login exclusivo: acesso restrito a administradores via e-mail e senha (armazenados no banco).

Gestão de usuários: cadastro, edição e exclusão de usuários (com remoção da pasta associada).

Exploração de pastas: navegação por todas as pastas de usuários, visualização de tamanhos e exclusão de itens.

Perfil do admin: edição de nome, e-mail e senha com verificação da senha atual.

Agendamento de notificações: envio manual ou automático (cron) de e-mails mensais informando a disponibilidade das notas fiscais.

Busca e ordenação: no dashboard admin, busca por nome, CPF/CNPJ ou e-mail, com ordenação por ID, nome ou data.

📧 Comunicação e Notificações

Templates de e-mail: dois modelos HTML editáveis

email_codigo.php – para envio do código de verificação.

email_notificacao_mensal.php – para notificação de notas fiscais disponíveis.

Envio de teste: permite ao admin enviar um e-mail de teste para validar o template e as configurações.

🎨 Interface e Personalização

Temas claro/escuro: alternância com persistência via localStorage.

Layout responsivo: adaptado para desktop, tablet e celular.

Design moderno: cards coloridos, ícones Font Awesome e sombras sutis.

Cores institucionais: azul escuro no cabeçalho/rodapé (tema claro) e preto (tema escuro), com botões em preto e letras brancas.

🔒 Segurança e Infraestrutura

Proteção de pastas: .htaccess na pasta uploads/ bloqueia acesso direto aos arquivos via HTTP.

Backup e conformidade: o sistema incentiva a guarda segura de documentos, com conformidade à LGPD (opção de anonimização e controle de acesso).

⚙️ Configuração e Extensibilidade

Arquivo config.php: centraliza todas as configurações (banco de dados, e-mail, Turnstile, tema, quota, etc.).

Cron job: agendamento automático de notificações mensais (script em cron/envio_mensal.php).

Modularidade: funções de e-mail, validação e métricas isoladas em functions.php.

📌 Fluxos Principais

Login do usuário:

Insere CPF/CNPJ → recebe código por e-mail → valida código → acessa dashboard.

Dashboard do usuário:

Visualiza métricas (XMLs, DANFes, uso de disco).

Navega por pastas e baixa arquivos ou pastas inteiras.

Painel admin:

Gerencia usuários, pastas, envia notificações e edita perfil.

Explora e exclui arquivos/pastas de qualquer usuário.

Agendamento:

Admin envia notificação manual ou agenda via cron para o 1º dia de cada mês.

🧩 Requisitos Técnicos

PHP 7.4+ com extensões: PDO, zip, mbstring, fileinfo.

MySQL 5.7+.

Composer para gerenciar PHPMailer.

Servidor Apache com mod_rewrite e AllowOverride ativado.

Cron (opcional) para envio automático.

🚀 Benefícios

Segurança robusta: 2FA, Turnstile, hash de senha, bloqueio de acesso direto.

Facilidade de uso: interface limpa, métricas claras e ações intuitivas.

Controle total: admin tem visibilidade e gestão completa dos dados.

Conformidade: estrutura preparada para atender à LGPD e boas práticas de armazenamento.

Escalabilidade: código modular permite adicionar novas funcionalidades com facilidade.
