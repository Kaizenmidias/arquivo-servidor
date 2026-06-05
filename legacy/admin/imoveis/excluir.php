<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_login();

if (!isset($_GET['id'])) {
    redirect(BASE_URL . '/admin/imoveis/');
}

$imovel_id = (int)$_GET['id'];

// Excluir (o ON DELETE CASCADE cuida das relações
$pdo->prepare("DELETE FROM imoveis WHERE id = ?")->execute([$imovel_id]);

$_SESSION['sucesso'] = 'Imóvel excluído com sucesso!';
redirect(BASE_URL . '/admin/imoveis/');
