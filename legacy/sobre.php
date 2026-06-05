<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Sobre Nós</h1>
        <p class="text-xl text-gray-600">Conheça nossa história e nossa equipe</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-16">
        <div>
            <div class="bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl h-80 flex items-center justify-center">
                <span class="text-8xl">🏢</span>
            </div>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Nossa História</h2>
            <p class="text-gray-600 mb-4 leading-relaxed">
                A <?php echo SITE_NOME; ?> nasceu com o objetivo de transformar a experiência de comprar, vender e alugar imóveis em algo simples, transparente e humanizado.
            </p>
            <p class="text-gray-600 leading-relaxed">
                Com anos de experiência no mercado imobiliário, nossa equipe está preparada para ajudar você a encontrar o imóvel dos sonhos ou a fazer o melhor negócio com seu imóvel.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-8 mb-16">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Nossos Valores</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="text-5xl mb-4">🤝</div>
                <h3 class="font-bold text-gray-800 mb-2">Transparência</h3>
                <p class="text-gray-600">Informações claras e honestas em todas as etapas do processo</p>
            </div>
            <div class="text-center">
                <div class="text-5xl mb-4">💡</div>
                <h3 class="font-bold text-gray-800 mb-2">Inovação</h3>
                <p class="text-gray-600">Sempre buscando novas formas de melhor atendê-lo</p>
            </div>
            <div class="text-center">
                <div class="text-5xl mb-4">❤️</div>
                <h3 class="font-bold text-gray-800 mb-2">Paixão</h3>
                <p class="text-gray-600">Amamos o que fazemos e isso reflete no nosso atendimento</p>
            </div>
        </div>
    </div>

    <div class="text-center bg-gradient-to-br from-blue-600 to-purple-700 rounded-xl p-12 text-white">
        <h2 class="text-3xl font-bold mb-4">Vamos conversar?</h2>
        <p class="text-xl text-blue-100 mb-8">Estamos à disposição para ajudá-lo</p>
        <a href="https://wa.me/<?php echo get_config('whatsapp'); ?>" target="_blank"
            class="inline-block bg-white hover:bg-gray-100 text-blue-600 font-bold px-8 py-4 rounded-xl transition">
            💬 Falar no WhatsApp
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
