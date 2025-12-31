<?php
require_once '../init.php';
// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../comercial_add.php");
    exit();
}

$cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
$duracao = filter_input(INPUT_POST, 'duracao', FILTER_VALIDATE_INT);
$identificador_arquivo = trim(filter_input(INPUT_POST, 'identificador_arquivo', FILTER_SANITIZE_STRING));
$ativo = isset($_POST['ativo']) ? 1 : 0;

if (!$cliente_id || !$duracao || empty($identificador_arquivo)) {
    $_SESSION['error_message'] = "Todos os campos são obrigatórios.";
    header("Location: ../comercial_add.php");
    exit();
}

// Inserir no banco de dados
$sql = "INSERT INTO comerciais (cliente_id, identificador_arquivo, duracao, ativo) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("isii", $cliente_id, $identificador_arquivo, $duracao, $ativo);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Comercial adicionado com sucesso!";
    } else {
        $_SESSION['error_message'] = "Erro ao salvar no banco de dados: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Erro ao preparar a query: " . $conn->error;
}

$conn->close();
header("Location: ../comerciais.php");
exit();
?>