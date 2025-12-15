<?php
require_once '../init.php';

if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$investimento_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$investimento_id) {
    header("Location: ../investimentos_socios.php?error=id_invalido");
    exit();
}

$stmt = $conn->prepare("DELETE FROM investimentos_socios WHERE id = ?");
$stmt->bind_param("i", $investimento_id);

if ($stmt->execute()) {
    header("Location: ../investimentos_socios.php?success=excluido");
} else {
    error_log("Erro ao excluir investimento: " . $stmt->error);
    header("Location: ../investimentos_socios.php?error=db_error");
}

$stmt->close();
$conn->close();
exit();
