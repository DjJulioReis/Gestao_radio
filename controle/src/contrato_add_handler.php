<?php
require_once '../init.php';

// Apenas administradores podem adicionar contratos
if ($_SESSION['user_level'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php");
    exit();
}

// Validação dos dados recebidos
$cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
$plano_id = filter_input(INPUT_POST, 'plano_id', FILTER_VALIDATE_INT);
$data_inicio = $_POST['data_inicio']; // Adicionar validação de data se necessário
$data_fim = $_POST['data_fim'];       // Adicionar validação de data se necessário

if (!$cliente_id || !$plano_id || empty($data_inicio) || empty($data_fim)) {
    // Redireciona de volta com erro se dados forem inválidos
    header("Location: ../contrato_add.php?error=dados_invalidos");
    exit();
}

// Prepara e executa a query de inserção
$stmt = $conn->prepare(
    "INSERT INTO contratos (cliente_id, plano_id, data_inicio, data_fim) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("iiss", $cliente_id, $plano_id, $data_inicio, $data_fim);

if ($stmt->execute()) {
    $contrato_id = $stmt->insert_id;
    // Lógica futura de geração de cobranças pode ser adicionada aqui

    // Redireciona para a lista de contratos com sucesso
    header("Location: ../contratos.php?success=1");
    exit();
} else {
    // Log do erro e redirecionamento
    error_log("Erro ao adicionar contrato: " . $stmt->error);
    header("Location: ../contrato_add.php?error=db_error");
}

$stmt->close();
$conn->close();
exit();
