<?php
// init.php - Arquivo de inicialização central

// Habilita a exibição de todos os erros para depuração apenas em ambiente de desenvolvimento
if (getenv('APP_ENV') !== 'production') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_log("Log de erros da aplicação");
}

// Carrega as configurações da aplicação
require_once 'config/app.php';

// Carrega as credenciais do banco de dados
require_once 'config/db.php';

// Inicia a sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Estabelece a conexão com o banco de dados
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verifica a conexão
if ($conn->connect_error) {
    error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
    die("Ocorreu um problema ao conectar com o sistema. Tente novamente mais tarde.");
}

// Define o charset para UTF-8 para evitar problemas com caracteres especiais
$conn->set_charset("utf8mb4");
?>
