<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../locutores.php");
    exit();
}

$id = $_GET['id'];

// Antes de excluir, seria ideal verificar se o locutor não está associado a clientes
// Para simplificar, vamos apenas excluir.
$stmt = $conn->prepare("DELETE FROM locutores WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../locutores.php?success=3");
} else {
    header("Location: ../locutores.php?error=3");
}

$stmt->close();
$conn->close();
exit();
