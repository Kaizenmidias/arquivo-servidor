<?php
require_once __DIR__ . '/includes/header.php';

if (!isset($_GET['slug'])) {
    redirect(BASE_URL . '/');
}

$slug = $_GET['slug'];

// Buscar imóvel
$stmt = $pdo->prepare("SELECT i.*, t.nome_tipo FROM imoveis i LEFT JOIN tipos_propriedade t ON i.tipo_propriedade_id = t.id WHERE i.slug = ? AND i.ativo = 1");
$stmt->execute([$slug]);
$imovel = $stmt->fetch();

if (!$imovel) {
    redirect(BASE_URL . '/');
}

// Buscar características
$caracteristicas = $pdo->prepare("SELECT c.* FROM imoveis_caracteristicas c INNER JOIN imovel_caracteristica ic ON c.id = ic.caracteristica_id WHERE ic.imovel_id = ?");
$caracteristicas->execute([$imovel['id']]);
$caracteristicas = $caracteristicas->fetchAll();

// Buscar imóveis relacionados
$relacionados = $pdo->prepare("SELECT i.*, t.nome_tipo FROM imoveis i LEFT JOIN tipos_propriedade t ON i.tipo_propriedade_id = t.id WHERE i.id != ? AND i.ativo = 1 AND i.cidade = ? ORDER BY RAND() LIMIT 4");
$relacionados->execute([$imovel['id'], $imovel['cidade']]);
$relacionados = $relacionados->fetchAll();

// Processar formulário de contato
$mensagem_sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contato_imovel'])) {
    $nome = sanitize($_POST['nome']);
    $telefone = sanitize($_POST['telefone']);
    $email = sanitize($_POST['email']);
    $mensagem = sanitize($_POST['mensagem'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO leads (imovel_id, nome, telefone, email, mensagem, origem) VALUES (?, ?, ?, ?, ?, 'Site - Página do Imóvel')");
    $stmt->execute([$imovel['id'], $nome, $telefone, $email, $mensagem]);

    $mensagem_sucesso = 'Obrigado! Entraremos em contato em breve.';
}
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-6">
        <a href="<?php echo BASE_URL; ?>/" class="hover:text-blue-600">Início</a>
        <span class="mx-2">/</span>
        <a href="<?php echo BASE_URL; ?>/busca.php" class="hover:text-blue-600">Imóveis</a>
        <span class="mx-2">/</span>
        <span class="text-gray-800"><?php echo $imovel['titulo']; ?></span>
    </nav>

    <!-- Título e Valor -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <div class="flex items-center space-x-3 mb-2">
                <span class="px-3 py-1 <?php echo $imovel['operacao'] === 'Venda' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?> rounded-full text-sm font-semibold">
                    <?php echo $imovel['operacao']; ?>
                </span>
                <span class="text-gray-500 text-sm"><?php echo $imovel['nome_tipo']; ?></span>
                <?php if ($imovel['destaque']): ?>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                        ⭐ Destaque
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800"><?php echo $imovel['titulo']; ?></h1>
            <p class="text-gray-500 mt-2">
                📍 <?php echo $imovel['endereco']; ?>, <?php echo $imovel['bairro']; ?> - <?php echo $imovel['cidade']; ?>/<?php echo $imovel['estado']; ?>
            </p>
        </div>
        <div class="mt-4 md:mt-0 text-right">
            <p class="text-gray-500 text-sm">Preço</p>
            <p class="text-3xl md:text-4xl font-bold text-blue-600">
                <?php echo formatar_preco($imovel['valor']); ?>
            </p>
            <?php if ($imovel['condominio']): ?>
                <p class="text-gray-500 text-sm mt-1">Condomínio: <?php echo formatar_preco($imovel['condominio']); ?></p>
            <?php endif; ?>
            <?php if ($imovel['iptu']): ?>
                <p class="text-gray-500 text-sm">IPTU: <?php echo formatar_preco($imovel['iptu']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Galeria (placeholder) -->
            <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl h-80 flex items-center justify-center">
                <div class="text-center text-gray-400">
                    <span class="text-8xl block mb-4">🏠</span>
                    <p class="text-lg">Galeria de fotos</p>
                    <p class="text-sm">Adicione fotos no painel administrativo</p>
                </div>
            </div>

            <!-- Características -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">🏗️ Características</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-2xl mb-1 block">🛏️</span>
                        <p class="text-gray-500 text-sm">Quartos</p>
                        <p class="text-xl font-bold text-gray-800"><?php echo $imovel['quartos'] ?: '-'; ?></p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-2xl mb-1 block">🚿</span>
                        <p class="text-gray-500 text-sm">Banheiros</p>
                        <p class="text-xl font-bold text-gray-800"><?php echo $imovel['banheiros'] ?: '-'; ?></p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-2xl mb-1 block">🚗</span>
                        <p class="text-gray-500 text-sm">Garagens</p>
                        <p class="text-xl font-bold text-gray-800"><?php echo $imovel['garagens'] ?: '-'; ?></p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-2xl mb-1 block">📐</span>
                        <p class="text-gray-500 text-sm">Área Útil</p>
                        <p class="text-xl font-bold text-gray-800"><?php echo $imovel['area_util'] ? $imovel['area_util'] . ' m²' : '-'; ?></p>
                    </div>
                    <?php if ($imovel['suites']): ?>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <span class="text-2xl mb-1 block">🛁</span>
                            <p class="text-gray-500 text-sm">Suítes</p>
                            <p class="text-xl font-bold text-gray-800"><?php echo $imovel['suites']; ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($imovel['area_total']): ?>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <span class="text-2xl mb-1 block">🏞️</span>
                            <p class="text-gray-500 text-sm">Área Total</p>
                            <p class="text-xl font-bold text-gray-800"><?php echo $imovel['area_total']; ?> m²</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($caracteristicas): ?>
                    <div class="mt-6">
                        <h3 class="font-semibold text-gray-800 mb-3">Comodidades</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($caracteristicas as $c): ?>
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm flex items-center space-x-1">
                                    <span><?php echo $c['icone']; ?></span>
                                    <span><?php echo $c['nome']; ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Descrição -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📝 Descrição</h2>
                <div class="text-gray-600 leading-relaxed whitespace-pre-wrap">
                    <?php echo nl2br($imovel['descricao']); ?>
                </div>
            </div>

            <!-- Localização no Mapa -->
            <?php if ($imovel['latitud'] && $imovel['longitud']): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">🗺️ Localização</h2>
                    <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center">
                        <p class="text-gray-500">Integre com Google Maps ou similar</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar - Contato -->
        <div class="space-y-6">
            <!-- Card Contato -->
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                <h3 class="text-xl font-bold text-gray-800 mb-4">💬 Tenho Interesse</h3>

                <?php if ($mensagem_sucesso): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <?php echo $mensagem_sucesso; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="contato_imovel" value="1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="nome" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                        <input type="text" name="telefone" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem</label>
                        <textarea name="mensagem" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                        Enviar Mensagem
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t">
                    <a href="https://wa.me/<?php echo get_config('whatsapp'); ?>?text=Olá! Tenho interesse no imóvel: <?php echo urlencode($imovel['titulo']); ?>" target="_blank"
                        class="w-full block bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg text-center transition">
                        💬 Falar no WhatsApp
                    </a>
                </div>

                <div class="mt-4 text-sm text-gray-500">
                    <p class="font-medium text-gray-700 mb-1">Código do anúncio:</p>
                    <p><?php echo $imovel['codigo_anuncio']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Imóveis Relacionados -->
    <?php if ($relacionados): ?>
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-800 mb-8">🏡 Imóveis Relacionados</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($relacionados as $rel): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-2xl transition">
                        <div class="h-44 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <span class="text-5xl">🏠</span>
                        </div>
                        <div class="p-5">
                            <span class="px-2 py-1 <?php echo $rel['operacao'] === 'Venda' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?> rounded-full text-xs font-semibold mb-2 inline-block">
                                <?php echo $rel['operacao']; ?>
                            </span>
                            <h3 class="font-bold text-gray-800 mb-1 truncate"><?php echo $rel['titulo']; ?></h3>
                            <p class="text-gray-500 text-xs mb-3 truncate"><?php echo $rel['bairro']; ?>, <?php echo $rel['cidade']; ?></p>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-blue-600"><?php echo formatar_preco($rel['valor']); ?></span>
                                <a href="<?php echo BASE_URL; ?>/imovel.php?slug=<?php echo $rel['slug']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Ver →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
