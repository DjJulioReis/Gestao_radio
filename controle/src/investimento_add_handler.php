<?php
require_once '../init.php';

if ($_SESSION['user_level'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php");
    exit();
}

$locutor_id = filter_input(INPUT_POST, 'locutor_id', FILTER_VALIDATE_INT);
$valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
$data = $_POST['data'];
$descricao = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING));

if (!$locutor_id || !$valor || empty($data)) {
    header("Location: ../investimentos_socios.php?error=dados_invalidos");
    exit();
}

$conn->begin_transaction();

try {
    // Adiciona o investimento
    $stmt = $conn->prepare("INSERT INTO investimentos_socios (locutor_id, valor, data, descricao) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $locutor_id, $valor, $data, $descricao);
    $stmt->execute();
    $stmt->close();

    // Atualiza o saldo do sócio
    $stmt = $conn->prepare("UPDATE locutores SET saldo_investido = saldo_investido + ? WHERE id = ?");
    $stmt->bind_param("di", $valor, $locutor_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header("Location: ../investimentos_socios.php?success=adicionado");

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao adicionar investimento: " . $e->getMessage());
    header("Location: ../investimentos_socios.php?error=db_error");
}

if ($stmt->execute()) {
    header("Location: ../investimentos_socios.php?success=adicionado");
} else {
    error_log("Erro ao adicionar investimento: " . $stmt->error);
    header("Location: ../investimentos_socios.php?error=db_error");
}

$stmt->close();
$conn->close();
exit();
