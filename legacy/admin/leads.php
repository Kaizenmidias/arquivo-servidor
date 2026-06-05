<?php
require_once __DIR__ . '/../includes/admin-header.php';

// Buscar todos os leads
$stmt = $pdo->query("SELECT l.*, i.titulo as imovel_titulo FROM leads l LEFT JOIN imoveis i ON l.imovel_id = i.id ORDER BY l.created_at DESC");
$leads = $stmt->fetchAll();
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">📧 Leads Recebidos</h1>
        <a href="<?php echo BASE_URL; ?>/admin/" class="text-gray-600 hover:text-gray-800">← Voltar</a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Contato</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Imóvel</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Origem</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Mensagem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($leads): ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo formatar_data($lead['created_at']); ?>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-800">
                                    <?php echo $lead['nome']; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <p>📞 <?php echo $lead['telefone']; ?></p>
                                    <p>📧 <?php echo $lead['email']; ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo $lead['imovel_titulo'] ?? '-'; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <?php echo $lead['origem']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                    <?php echo $lead['mensagem'] ?? '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Nenhum lead recebido ainda.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
