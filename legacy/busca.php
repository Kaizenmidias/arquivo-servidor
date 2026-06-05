<?php
require_once __DIR__ . '/includes/header.php';

// Construir query
$sql = "SELECT i.*, t.nome_tipo FROM imoveis i LEFT JOIN tipos_propriedade t ON i.tipo_propriedade_id = t.id WHERE i.ativo = 1";
$params = [];

if (!empty($_GET['operacao'])) {
    $sql .= " AND i.operacao = ?";
    $params[] = $_GET['operacao'];
}

if (!empty($_GET['tipo'])) {
    $sql .= " AND t.nome_tipo = ?";
    $params[] = $_GET['tipo'];
}

if (!empty($_GET['cidade'])) {
    $sql .= " AND i.cidade LIKE ?";
    $params[] = "%" . $_GET['cidade'] . "%";
}

if (!empty($_GET['bairro'])) {
    $sql .= " AND i.bairro LIKE ?";
    $params[] = "%" . $_GET['bairro'] . "%";
}

if (!empty($_GET['quartos_min'])) {
    $sql .= " AND i.quartos >= ?";
    $params[] = (int)$_GET['quartos_min'];
}

if (!empty($_GET['valor_min'])) {
    $sql .= " AND i.valor >= ?";
    $params[] = (float)$_GET['valor_min'];
}

if (!empty($_GET['valor_max'])) {
    $sql .= " AND i.valor <= ?";
    $params[] = (float)$_GET['valor_max'];
}

// Ordenar
$order_by = "i.created_at DESC";
if (!empty($_GET['ordenar'])) {
    switch ($_GET['ordenar']) {
        case 'valor_asc': $order_by = "i.valor ASC"; break;
        case 'valor_desc': $order_by = "i.valor DESC"; break;
        case 'destaque': $order_by = "i.destaque DESC, i.created_at DESC"; break;
    }
}
$sql .= " ORDER BY $order_by";

// Executar
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$imoveis = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">🏡 Encontrar Imóveis</h1>
    <p class="text-gray-600 mb-8"><?php echo count($imoveis); ?> imóvel(is) encontrado(s)</p>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                <h3 class="font-bold text-gray-800 text-lg mb-6">🔍 Filtros</h3>
                
                <form method="GET" class="space-y-6">
                    <!-- Operação -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Operação</label>
                        <select name="operacao" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todas</option>
                            <option value="Venda" <?php echo (!empty($_GET['operacao']) && $_GET['operacao'] === 'Venda') ? 'selected' : ''; ?>>Venda</option>
                            <option value="Aluguel" <?php echo (!empty($_GET['operacao']) && $_GET['operacao'] === 'Aluguel') ? 'selected' : ''; ?>>Aluguel</option>
                            <option value="Temporada" <?php echo (!empty($_GET['operacao']) && $_GET['operacao'] === 'Temporada') ? 'selected' : ''; ?>>Temporada</option>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <?php
                            $tipos = $pdo->query("SELECT DISTINCT nome_tipo FROM tipos_propriedade ORDER BY nome_tipo")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($tipos as $tipo):
                            ?>
                                <option value="<?php echo $tipo; ?>" <?php echo (!empty($_GET['tipo']) && $_GET['tipo'] === $tipo) ? 'selected' : ''; ?>><?php echo $tipo; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Localização -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                        <input type="text" name="cidade" value="<?php echo $_GET['cidade'] ?? ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Ex: São Paulo">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                        <input type="text" name="bairro" value="<?php echo $_GET['bairro'] ?? ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Ex: Centro">
                    </div>

                    <!-- Quartos -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mínimo de Quartos</label>
                        <select name="quartos_min" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Qualquer</option>
                            <option value="1" <?php echo (!empty($_GET['quartos_min']) && $_GET['quartos_min'] == 1) ? 'selected' : ''; ?>>1+</option>
                            <option value="2" <?php echo (!empty($_GET['quartos_min']) && $_GET['quartos_min'] == 2) ? 'selected' : ''; ?>>2+</option>
                            <option value="3" <?php echo (!empty($_GET['quartos_min']) && $_GET['quartos_min'] == 3) ? 'selected' : ''; ?>>3+</option>
                            <option value="4" <?php echo (!empty($_GET['quartos_min']) && $_GET['quartos_min'] == 4) ? 'selected' : ''; ?>>4+</option>
                        </select>
                    </div>

                    <!-- Valor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="valor_min" value="<?php echo $_GET['valor_min'] ?? ''; ?>"
                                class="px-4 py-2 border border-gray-300 rounded-lg" placeholder="Min">
                            <input type="number" name="valor_max" value="<?php echo $_GET['valor_max'] ?? ''; ?>"
                                class="px-4 py-2 border border-gray-300 rounded-lg" placeholder="Max">
                        </div>
                    </div>

                    <!-- Ordenar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
                        <select name="ordenar" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Mais recentes</option>
                            <option value="valor_asc" <?php echo (!empty($_GET['ordenar']) && $_GET['ordenar'] === 'valor_asc') ? 'selected' : ''; ?>>Menor preço</option>
                            <option value="valor_desc" <?php echo (!empty($_GET['ordenar']) && $_GET['ordenar'] === 'valor_desc') ? 'selected' : ''; ?>>Maior preço</option>
                            <option value="destaque" <?php echo (!empty($_GET['ordenar']) && $_GET['ordenar'] === 'destaque') ? 'selected' : ''; ?>>Destaques primeiro</option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                            Aplicar Filtros
                        </button>
                    </div>

                    <div>
                        <a href="<?php echo BASE_URL; ?>/busca.php" class="w-full block text-center text-gray-600 hover:text-gray-800 font-medium py-2">
                            Limpar filtros
                        </a>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Resultados -->
        <main class="lg:col-span-3">
            <?php if ($imoveis): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach ($imoveis as $imovel): ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-2xl transition">
                            <div class="h-52 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center relative">
                                <span class="text-6xl">🏠</span>
                                <?php if ($imovel['destaque']): ?>
                                    <span class="absolute top-3 right-3 bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-sm font-bold">
                                        ⭐ Destaque
                                    </span>
                                <?php endif; ?>
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
                                <p class="text-gray-500 text-sm mb-3">
                                    📍 <?php echo $imovel['bairro']; ?>, <?php echo $imovel['cidade']; ?> - <?php echo $imovel['estado']; ?>
                                </p>
                                
                                <!-- Características rápidas -->
                                <div class="flex flex-wrap gap-3 mb-4 text-sm text-gray-600">
                                    <?php if ($imovel['quartos']): ?>
                                        <span>🛏️ <?php echo $imovel['quartos']; ?> quarto(s)</span>
                                    <?php endif; ?>
                                    <?php if ($imovel['banheiros']): ?>
                                        <span>🚿 <?php echo $imovel['banheiros']; ?> banheiro(s)</span>
                                    <?php endif; ?>
                                    <?php if ($imovel['garagens']): ?>
                                        <span>🚗 <?php echo $imovel['garagens']; ?> vaga(s)</span>
                                    <?php endif; ?>
                                    <?php if ($imovel['area_util']): ?>
                                        <span>📐 <?php echo $imovel['area_util']; ?> m²</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t">
                                    <span class="text-2xl font-bold text-blue-600">
                                        <?php echo formatar_preco($imovel['valor']); ?>
                                    </span>
                                    <a href="<?php echo BASE_URL; ?>/imovel.php?slug=<?php echo $imovel['slug']; ?>"
                                        class="text-blue-600 hover:text-blue-800 font-semibold">
                                        Ver detalhes →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-md p-12 text-center">
                    <span class="text-6xl mb-4 block">🔍</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Nenhum imóvel encontrado</h3>
                    <p class="text-gray-500 mb-6">Tente ajustar os filtros de busca</p>
                    <a href="<?php echo BASE_URL; ?>/busca.php"
                        class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                        Limpar filtros
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
