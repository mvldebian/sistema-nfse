// Função para renderizar o Turnstile em um elemento
function renderTurnstile(elementId) {
    if (typeof turnstile !== 'undefined') {
        turnstile.render('#' + elementId, {
            sitekey: TURNSTILE_SITE_KEY,
            callback: function(token) {
                console.log('Turnstile verificado com sucesso!');
            },
            'error-callback': function() {
                console.log('Erro no Turnstile. Recarregue a página.');
            }
        });
    }
}

// Inicializa quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se o Turnstile está ativado (variável global)
    if (typeof TURNSTILE_ENABLED !== 'undefined' && TURNSTILE_ENABLED === true) {
        const container = document.getElementById('turnstile-container');
        if (container) {
            renderTurnstile('turnstile-container');
        }
    }
});
