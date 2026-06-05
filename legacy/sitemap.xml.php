<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

// Buscar imóveis ativos
$imoveis = $pdo->query("SELECT slug, updated_at FROM imoveis WHERE ativo = 1 ORDER BY created_at DESC")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Home -->
    <url>
        <loc><?php echo BASE_URL; ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Busca -->
    <url>
        <loc><?php echo BASE_URL; ?>/busca.php</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Sobre -->
    <url>
        <loc><?php echo BASE_URL; ?>/sobre.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Contato -->
    <url>
        <loc><?php echo BASE_URL; ?>/contato.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Imóveis -->
    <?php foreach ($imoveis as $imovel): ?>
        <url>
            <loc><?php echo BASE_URL; ?>/imovel.php?slug=<?php echo $imovel['slug']; ?></loc>
            <lastmod><?php echo date('Y-m-d', strtotime($imovel['updated_at'])); ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>
</urlset>
