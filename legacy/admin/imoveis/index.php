<?php
require_once __DIR__ . '/../../includes/admin-header.php';

// Buscar todos os imóveis
$stmt = $pdo->query("SELECT i.*, t.nome_tipo FROM imoveis i LEFT JOIN tipos_propriedade t ON i.tipo_propriedade_id = t.id ORDER BY i.created_at DESC");
$imoveis = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">🏠 Gerenciar Imóveis</h1>
        <a href="<?php echo BASE_URL; ?>/admin/imoveis/criar.php"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            ➕ Novo Imóvel
        </a>
    </div>

    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Código</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Título</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Operação</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Valor</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($imoveis): ?>
                        <?php foreach ($imoveis as $imovel): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-mono text-gray-600"><?php echo $imovel['codigo_anuncio']; ?></td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800"><?php echo $imovel['titulo']; ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $imovel['bairro']; ?> - <?php echo $imovel['cidade']; ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo $imovel['nome_tipo']; ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php
                                        echo $imovel['operacao'] === 'Venda' ? 'bg-blue-100 text-blue-800' :
                                             ($imovel['operacao'] === 'Aluguel' ? 'bg-green-100 text-green-800' :
                                             'bg-yellow-100 text-yellow-800');
                                    ?>">
                                        <?php echo $imovel['operacao']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800"><?php echo formatar_preco($imovel['valor']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php
                                        echo $imovel['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo $imovel['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                    <?php if ($imovel['destaque']): ?>
                                        <span class="ml-2 text-yellow-500">⭐</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?php echo BASE_URL; ?>/admin/imoveis/editar.php?id=<?php echo $imovel['id']; ?>"
                                        class="text-blue-600 hover:text-blue-800 mr-3">✏️ Editar</a>
                                    <a href="<?php echo BASE_URL; ?>/admin/imoveis/excluir.php?id=<?php echo $imovel['id']; ?>"
                                        onclick="return confirm('Tem certeza?')"
                                        class="text-red-600 hover:text-red-800">🗑️ Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Nenhum imóvel cadastrado ainda. Clique em "Novo Imóvel" para começar!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
