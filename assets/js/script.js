document.addEventListener('DOMContentLoaded', function() {
    const btnTema = document.getElementById('btnTema');
    if (!btnTema) return;

    // Lê a variável global (se existir) que define se deve forçar o tema claro como padrão inicial
    const forcarClaro = typeof FORCAR_TEMA_CLARO !== 'undefined' && FORCAR_TEMA_CLARO === true;

    function aplicarTema(tema) {
        const body = document.body;
        const icone = btnTema.querySelector('i');
        if (tema === 'claro') {
            body.classList.remove('tema-escuro');
            if (icone) icone.className = 'fas fa-moon';
        } else {
            body.classList.add('tema-escuro');
            if (icone) icone.className = 'fas fa-sun';
        }
        // Salva a preferência do usuário
        localStorage.setItem('tema', tema);
    }

    // Define o tema inicial: prioriza a preferência salva, senão usa o forçado ou escuro
    let temaInicial;
    const preferenciaSalva = localStorage.getItem('tema');
    if (preferenciaSalva) {
        // Se o usuário já escolheu, usa a preferência dele
        temaInicial = preferenciaSalva;
    } else {
        // Senão, usa o forçado (se true) ou escuro como padrão
        temaInicial = forcarClaro ? 'claro' : 'escuro';
    }
    aplicarTema(temaInicial);

    // Clique no botão: alterna e salva
    btnTema.addEventListener('click', function() {
        const isEscuro = document.body.classList.contains('tema-escuro');
        const novoTema = isEscuro ? 'claro' : 'escuro';
        aplicarTema(novoTema);
    });
});
