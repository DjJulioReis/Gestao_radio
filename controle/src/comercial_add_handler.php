<?php
require_once '../init.php';
require_once 'upload_helper.php'; // Reutilizando o helper de upload

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
$ativo = isset($_POST['ativo']) ? 1 : 0;
$arquivo = $_FILES['arquivo_comercial'];

if (!$cliente_id || !$duracao || empty($arquivo['name'])) {
    $_SESSION['error_message'] = "Todos os campos são obrigatórios.";
    header("Location: ../comercial_add.php");
    exit();
}

// Lógica de Upload
$relative_dir = "comerciais/{$cliente_id}";
$allowed_extensions = ['mp3', 'wav', 'm4a', 'aac'];
$allowed_mime_types = ['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/x-m4a', 'audio/aac'];

$upload_result = uploadArquivo($arquivo, $relative_dir, $allowed_extensions, $allowed_mime_types);

if ($upload_result['error']) {
    $_SESSION['error_message'] = $upload_result['error'];
    header("Location: ../comercial_add.php");
    exit();
}

$nome_arquivo = $upload_result['filename'];
// O caminho do arquivo para o RadioBOSS deve ser absoluto no sistema de arquivos do servidor.
// O PROJECT_ROOT já aponta para /app, então construímos o caminho a partir daí.
$caminho_arquivo = PROJECT_ROOT . '/controle' . $upload_result['filepath'];

// Inserir no banco de dados
$sql = "INSERT INTO comerciais (cliente_id, nome_arquivo, caminho_arquivo, duracao, ativo) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("issii", $cliente_id, $nome_arquivo, $caminho_arquivo, $duracao, $ativo);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Comercial adicionado com sucesso!";
    } else {
        // Se falhar, tenta remover o arquivo para não deixar lixo
        unlink(PROJECT_ROOT . $caminho_arquivo);
        $_SESSION['error_message'] = "Erro ao salvar no banco de dados: " . $stmt->error;
    }
    $stmt->close();
} else {
    unlink(PROJECT_ROOT . $caminho_arquivo);
    $_SESSION['error_message'] = "Erro ao preparar a query: " . $conn->error;
}

$conn->close();
header("Location: ../comerciais.php");
exit();
?>