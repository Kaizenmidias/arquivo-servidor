<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        redirect(BASE_URL . '/admin/');
    } else {
        $erro = 'E-mail ou senha incorretos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔐 Painel Admin</h1>
            <p class="text-gray-600">Entre com suas credenciais</p>
        </div>
        
        <?php if ($erro): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="seu@email.com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                <input type="password" name="senha" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Sua senha">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 hover:shadow-lg">
                Entrar
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Usuário padrão: <strong>admin@exemplo.com</strong></p>
            <p>Senha padrão: <strong>admin123</strong></p>
            <p class="mt-2 text-red-500 font-semibold">⚠️ ALTERE A SENHA APÓS O PRIMEIRO LOGIN!</p>
        </div>
    </div>
</body>
</html>
