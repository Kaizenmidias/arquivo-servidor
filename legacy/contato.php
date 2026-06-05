<?php
require_once __DIR__ . '/includes/header.php';

$mensagem_sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contato_site'])) {
    $nome = sanitize($_POST['nome']);
    $telefone = sanitize($_POST['telefone']);
    $email = sanitize($_POST['email']);
    $mensagem = sanitize($_POST['mensagem'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO leads (nome, telefone, email, mensagem, origem) VALUES (?, ?, ?, ?, 'Site - Página de Contato')");
    $stmt->execute([$nome, $telefone, $email, $mensagem]);

    $mensagem_sucesso = 'Obrigado! Entraremos em contato em breve.';
}
?>

<div class="max-w-6xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Entre em Contato</h1>
        <p class="text-xl text-gray-600">Estamos à disposição para atendê-lo</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Formulário -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Envie uma mensagem</h2>

            <?php if ($mensagem_sucesso): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo $mensagem_sucesso; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="contato_site" value="1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                        <input type="text" name="nome" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telefone *</label>
                        <input type="text" name="telefone" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem</label>
                    <textarea name="mensagem" rows="5"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-lg transition">
                    Enviar Mensagem
                </button>
            </form>
        </div>

        <!-- Informações de Contato -->
        <div class="space-y-8">
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Informações</h2>
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-100 p-3 rounded-xl">
                            <span class="text-2xl">📍</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Endereço</h3>
                            <p class="text-gray-600"><?php echo get_config('endereco'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="bg-green-100 p-3 rounded-xl">
                            <span class="text-2xl">📞</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Telefone</h3>
                            <p class="text-gray-600"><?php echo get_config('telefone'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="bg-purple-100 p-3 rounded-xl">
                            <span class="text-2xl">📧</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">E-mail</h3>
                            <p class="text-gray-600"><?php echo get_config('email_contato'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <a href="https://wa.me/<?php echo get_config('whatsapp'); ?>" target="_blank"
                class="block bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-xl shadow-lg text-center transition transform hover:scale-105">
                💬 Falar Conosco no WhatsApp
            </a>

            <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl h-64 flex items-center justify-center">
                <span class="text-gray-500">🗺️ Mapa (integre com Google Maps)</span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
