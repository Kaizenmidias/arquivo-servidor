<?php
require_once __DIR__ . '/includes/header.php';

// Buscar imóveis em destaque
$imoveis_destaque = $pdo->query("SELECT i.*, t.nome_tipo FROM imoveis i LEFT JOIN tipos_propriedade t ON i.tipo_propriedade_id = t.id WHERE i.ativo = 1 AND i.destaque = 1 ORDER BY i.created_at DESC LIMIT 6")->fetchAll();

// Buscar imóveis recentes
$imoveis_recentes = $pdo->query("SELECT i.*, t.nome_tipo FROM imoveis i LEFT JOIN tipos_propriedade t ON i.tipo_propriedade_id = t.id WHERE i.ativo = 1 ORDER BY i.created_at DESC LIMIT 6")->fetchAll();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-blue-600 to-purple-700 text-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                Encontre o imóvel dos seus sonhos
            </h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-10">
                Casas, apartamentos, terrenos e muito mais. Ajudamos você a encontrar o lugar perfeito.
            </p>

            <!-- Search Form -->
            <div class="bg-white rounded-xl shadow-2xl p-6">
                <form action="<?php echo BASE_URL; ?>/busca.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Operação</label>
                        <select name="operacao" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-800">
                            <option value="">Todas</option>
                            <option value="Venda">Venda</option>
                            <option value="Aluguel">Aluguel</option>
                            <option value="Temporada">Temporada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-800">
                            <option value="">Todos</option>
                            <?php
                            $tipos = $pdo->query("SELECT DISTINCT nome_tipo FROM tipos_propriedade ORDER BY nome_tipo")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($tipos as $tipo):
                            ?>
                                <option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                        <input type="text" name="cidade" placeholder="Digite a cidade"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-800">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                            🔍 Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Destaques -->
<?php if ($imoveis_destaque): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Imóveis</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">⭐ Em Destaque</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($imoveis_destaque as $imovel): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-2xl transition">
                    <div class="h-56 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <span class="text-6xl">🏠</span>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                <?php echo $imovel['operacao']; ?>
                            </span>
                            <span class="text-gray-500 text-sm"><?php echo $imovel['nome_tipo']; ?></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">
                            <?php echo $imovel['titulo']; ?>
                        </h3>
                        <p class="text-gray-500 text-sm mb-4">
                            📍 <?php echo $imovel['bairro']; ?>, <?php echo $imovel['cidade']; ?> - <?php echo $imovel['estado']; ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-blue-600">
                                <?php echo formatar_preco($imovel['valor']); ?>
                            </span>
                            <a href="<?php echo BASE_URL; ?>/imovel.php?slug=<?php echo $imovel['slug']; ?>"
                                class="text-blue-600 hover:text-blue-800 font-semibold">
                                Ver mais →
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="<?php echo BASE_URL; ?>/busca.php"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition">
                Ver todos os imóveis →
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Imóveis Recentes -->
<?php if ($imoveis_recentes): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="text-green-600 font-semibold text-sm uppercase tracking-wider">Novidades</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">🏡 Imóveis Recentes</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($imoveis_recentes as $imovel): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-2xl transition">
                    <div class="h-56 bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center">
                        <span class="text-6xl">🏠</span>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 <?php echo $imovel['operacao'] === 'Venda' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?> rounded-full text-sm font-semibold">
                                <?php echo $imovel['operacao']; ?>
                            </span>
                            <span class="text-gray-500 text-sm"><?php echo $imovel['nome_tipo']; ?></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">
                            <?php echo $imovel['titulo']; ?>
                        </h3>
                        <p class="text-gray-500 text-sm mb-4">
                            📍 <?php echo $imovel['bairro']; ?>, <?php echo $imovel['cidade']; ?> - <?php echo $imovel['estado']; ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-blue-600">
                                <?php echo formatar_preco($imovel['valor']); ?>
                            </span>
                            <a href="<?php echo BASE_URL; ?>/imovel.php?slug=<?php echo $imovel['slug']; ?>"
                                class="text-blue-600 hover:text-blue-800 font-semibold">
                                Ver mais →
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="<?php echo BASE_URL; ?>/busca.php"
                class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded-lg transition">
                Ver todos os imóveis →
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-yellow-400 to-orange-500">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
            Tem um imóvel para vender ou alugar?
        </h2>
        <p class="text-xl text-yellow-100 mb-8">
            Entre em contato conosco e descubra como podemos ajudar!
        </p>
        <a href="https://wa.me/<?php echo get_config('whatsapp'); ?>" target="_blank"
            class="inline-block bg-white hover:bg-gray-100 text-orange-600 font-bold px-10 py-4 rounded-xl shadow-lg transition transform hover:scale-105">
            💬 Falar no WhatsApp
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
