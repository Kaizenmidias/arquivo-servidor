<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Verificar se está logado (exceto na página de login)
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'login.php') {
    require_login();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - <?php echo SITE_NOME; ?></title>
    
    <!-- Tailwind CSS via CDN (facilidade e performance) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Scripts do Head (configurados pelo admin) -->
    <?php echo get_config('script_head'); ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Scripts do Topo do Body (configurados pelo admin) -->
    <?php echo get_config('script_body_top'); ?>

    <?php if (is_logged_in()): ?>
    <!-- Navbar Admin -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/admin/" class="text-xl font-bold hover:text-blue-200 transition">
                        🏠 Painel Admin
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="<?php echo BASE_URL; ?>/" target="_blank" class="hover:text-blue-200 transition">
                        Ver Site
                    </a>
                    <span class="text-blue-200">Olá, <?php echo $_SESSION['usuario_nome']; ?></span>
                    <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition">
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
