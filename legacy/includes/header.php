<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NOME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Scripts HEAD do admin -->
    <?php echo get_config('script_head'); ?>
</head>
<body class="bg-gray-50">
    <!-- Scripts TOP BODY do admin -->
    <?php echo get_config('script_body_top'); ?>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>/" class="text-2xl font-bold text-blue-600">
                    🏠 <?php echo SITE_NOME; ?>
                </a>

                <!-- Menu Desktop -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="<?php echo BASE_URL; ?>/" class="text-gray-700 hover:text-blue-600 font-medium">Início</a>
                    <a href="<?php echo BASE_URL; ?>/busca.php" class="text-gray-700 hover:text-blue-600 font-medium">Imóveis</a>
                    <a href="<?php echo BASE_URL; ?>/sobre.php" class="text-gray-700 hover:text-blue-600 font-medium">Sobre</a>
                    <a href="<?php echo BASE_URL; ?>/contato.php" class="text-gray-700 hover:text-blue-600 font-medium">Contato</a>
                </nav>

                <!-- Contato -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="https://wa.me/<?php echo get_config('whatsapp'); ?>" target="_blank"
                        class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg font-medium flex items-center space-x-2 transition">
                        <span>💬</span>
                        <span>WhatsApp</span>
                    </a>
                </div>

                <!-- Menu Mobile -->
                <button id="menuMobileBtn" class="md:hidden text-gray-700 text-2xl">
                    ☰
                </button>
            </div>
        </div>

        <!-- Menu Mobile -->
        <div id="menuMobile" class="hidden md:hidden bg-white border-t">
            <div class="px-4 py-4 space-y-3">
                <a href="<?php echo BASE_URL; ?>/" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Início</a>
                <a href="<?php echo BASE_URL; ?>/busca.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Imóveis</a>
                <a href="<?php echo BASE_URL; ?>/sobre.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Sobre</a>
                <a href="<?php echo BASE_URL; ?>/contato.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Contato</a>
                <a href="https://wa.me/<?php echo get_config('whatsapp'); ?>" target="_blank"
                    class="block bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-lg font-medium text-center transition">
                    💬 WhatsApp
                </a>
            </div>
        </div>
    </header>

    <script>
        document.getElementById('menuMobileBtn').addEventListener('click', function() {
            const menu = document.getElementById('menuMobile');
            menu.classList.toggle('hidden');
        });
    </script>
