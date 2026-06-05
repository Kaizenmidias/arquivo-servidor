<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'portal_imobiliario');
define('DB_USER', 'root'); // Altere para seu usuário do MySQL
define('DB_PASS', '');     // Altere para sua senha do MySQL

// URL Base do Site (sem barra no final)
define('BASE_URL', 'http://localhost/Sistema-imob'); // Altere para sua URL

// Caminho Base do Servidor (sem barra no final)
define('BASE_PATH', __DIR__ . '/..');

// Conexão com o Banco de Dados
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Iniciar Sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Função para carregar configurações do site
function get_config($chave) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
    $stmt->execute([$chave]);
    $result = $stmt->fetch();
    return $result ? $result['valor'] : '';
}

// Definir constantes com configurações do site
define('SITE_NOME', get_config('nome_empresa'));
define('SITE_EMAIL', get_config('email_contato'));
define('SITE_TELEFONE', get_config('telefone'));
define('SITE_WHATSAPP', get_config('whatsapp'));
