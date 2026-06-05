<?php
require_once __DIR__ . '/../includes/admin-header.php';

// Buscar configurações existentes
$configs = $pdo->query("SELECT * FROM configuracoes")->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $chave => $valor) {
        $chave = sanitize($chave);
        $valor = $chave === 'script_head' || $chave === 'script_body_top' || $chave === 'script_body_bottom' ? $valor : sanitize($valor);
        
        $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$chave, $valor, $valor]);
    }
    
    $_SESSION['sucesso'] = 'Configurações salvas com sucesso!';
    redirect(BASE_URL . '/admin/configuracoes.php');
}
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">⚙️ Configurações</h1>
        <a href="<?php echo BASE_URL; ?>/admin/" class="text-gray-600 hover:text-gray-800">← Voltar</a>
    </div>

    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-xl shadow-md p-8 space-y-8">
        <!-- Dados da Empresa -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">🏢 Dados da Empresa</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Imobiliária</label>
                    <input type="text" name="nome_empresa" value="<?php echo $configs['nome_empresa'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">E-mail de Contato</label>
                    <input type="email" name="email_contato" value="<?php echo $configs['email_contato'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                    <input type="text" name="telefone" value="<?php echo $configs['telefone'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp (com DDI, ex: 5511999999999)</label>
                    <input type="text" name="whatsapp" value="<?php echo $configs['whatsapp'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
                    <input type="text" name="endereco" value="<?php echo $configs['endereco'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Scripts de Integração -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">🔌 Scripts e Integrações</h2>
            <p class="text-gray-500 text-sm mb-4">Adicione scripts como Google Analytics, Meta Pixel, Google Tag Manager, etc.</p>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Script no HEAD</label>
                    <textarea name="script_head" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"><?php echo $configs['script_head'] ?? ''; ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Script no início do BODY</label>
                    <textarea name="script_body_top" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"><?php echo $configs['script_body_top'] ?? ''; ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Script no final do BODY</label>
                    <textarea name="script_body_bottom" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"><?php echo $configs['script_body_bottom'] ?? ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Botão Salvar -->
        <div class="pt-4 border-t">
            <button type="submit"
                class="w-full md:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                💾 Salvar Configurações
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
