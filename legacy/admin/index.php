<?php
require_once __DIR__ . '/../includes/admin-header.php';

// Contadores para o dashboard
$total_imoveis = $pdo->query("SELECT COUNT(*) FROM imoveis")->fetchColumn();
$imoveis_ativos = $pdo->query("SELECT COUNT(*) FROM imoveis WHERE ativo = 1")->fetchColumn();
$imoveis_destaque = $pdo->query("SELECT COUNT(*) FROM imoveis WHERE destaque = 1")->fetchColumn();
$total_leads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$leads_recentes = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5")->fetchAll();
$imoveis_recentes = $pdo->query("SELECT * FROM imoveis ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">📊 Dashboard</h1>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total de Imóveis</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $total_imoveis; ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    🏠
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Imóveis Ativos</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $imoveis_ativos; ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    ✅
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Destaques</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $imoveis_destaque; ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    ⭐
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total de Leads</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $total_leads; ?></p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    👥
                </div>
            </div>
        </div>
    </div>

    <!-- Menu de Ações Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="<?php echo BASE_URL; ?>/admin/imoveis/criar.php"
            class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white p-6 rounded-xl shadow-md transition hover:shadow-lg text-center">
            <span class="text-3xl mb-2 block">➕</span>
            <span class="font-semibold">Novo Imóvel</span>
        </a>

        <a href="<?php echo BASE_URL; ?>/admin/imoveis/"
            class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white p-6 rounded-xl shadow-md transition hover:shadow-lg text-center">
            <span class="text-3xl mb-2 block">📋</span>
            <span class="font-semibold">Gerenciar Imóveis</span>
        </a>

        <a href="<?php echo BASE_URL; ?>/admin/configuracoes.php"
            class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white p-6 rounded-xl shadow-md transition hover:shadow-lg text-center">
            <span class="text-3xl mb-2 block">⚙️</span>
            <span class="font-semibold">Configurações</span>
        </a>

        <a href="<?php echo BASE_URL; ?>/admin/leads.php"
            class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white p-6 rounded-xl shadow-md transition hover:shadow-lg text-center">
            <span class="text-3xl mb-2 block">📧</span>
            <span class="font-semibold">Ver Leads</span>
        </a>
    </div>

    <!-- Grid: Últimos Imóveis e Leads Recentes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Últimos Imóveis -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">🏠 Últimos Imóveis Cadastrados</h2>
            <div class="space-y-4">
                <?php if ($imoveis_recentes): ?>
                    <?php foreach ($imoveis_recentes as $imovel): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo $imovel['titulo']; ?></p>
                                <p class="text-sm text-gray-500">
                                    <?php echo $imovel['bairro']; ?> - <?php echo $imovel['cidade']; ?>/<?php echo $imovel['estado']; ?>
                                </p>
                            </div>
                            <span class="text-blue-600 font-bold"><?php echo formatar_preco($imovel['valor']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Nenhum imóvel cadastrado ainda.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Leads Recentes -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">👥 Leads Recentes</h2>
            <div class="space-y-4">
                <?php if ($leads_recentes): ?>
                    <?php foreach ($leads_recentes as $lead): ?>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="font-semibold text-gray-800"><?php echo $lead['nome']; ?></p>
                            <p class="text-sm text-gray-500">
                                📞 <?php echo $lead['telefone']; ?> | 📧 <?php echo $lead['email']; ?>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                <?php echo formatar_data($lead['created_at']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Nenhum lead recebido ainda.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
