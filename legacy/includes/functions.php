<?php
// Função para criar slug amigável
function slugify($text) {
    // Remove acentos
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? '' : $text;
}

// Função para sanitizar inputs
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Função para formatar preço (BRL)
function formatar_preco($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Função para verificar se usuário está logado
function is_logged_in() {
    return isset($_SESSION['usuario_id']);
}

// Função para exigir login
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

// Função para redirecionar
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Função para gerar código único para anúncio
function gerar_codigo_anuncio() {
    return 'IMV-' . strtoupper(substr(uniqid(), -8));
}

// Função para formatar data
function formatar_data($data, $formato = 'd/m/Y H:i') {
    return date($formato, strtotime($data));
}
