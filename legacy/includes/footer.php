
    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Coluna 1 -->
                <div class="md:col-span-2">
                    <h3 class="text-xl font-bold mb-4">🏠 <?php echo SITE_NOME; ?></h3>
                    <p class="text-gray-400 mb-4">
                        Encontre o imóvel dos seus sonhos com a ajuda da nossa equipe de profissionais.
                    </p>
                    <div class="flex space-x-4">
                        <a href="https://wa.me/<?php echo get_config('whatsapp'); ?>" target="_blank" class="text-gray-400 hover:text-white text-2xl transition">💬</a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl transition">📘</a>
                        <a href="#" class="text-gray-400 hover:text-white text-2xl transition">📷</a>
                    </div>
                </div>

                <!-- Coluna 2 -->
                <div>
                    <h4 class="font-semibold mb-4">Links Rápidos</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?php echo BASE_URL; ?>/" class="hover:text-white transition">Início</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/busca.php" class="hover:text-white transition">Imóveis</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/sobre.php" class="hover:text-white transition">Sobre</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/contato.php" class="hover:text-white transition">Contato</a></li>
                    </ul>
                </div>

                <!-- Coluna 3 -->
                <div>
                    <h4 class="font-semibold mb-4">Contato</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>📍 <?php echo get_config('endereco'); ?></li>
                        <li>📞 <?php echo get_config('telefone'); ?></li>
                        <li>📧 <?php echo get_config('email_contato'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NOME; ?>. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts BOTTOM BODY do admin -->
    <?php echo get_config('script_body_bottom'); ?>
</body>
</html>
