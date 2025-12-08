<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../contratos.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM contratos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../contratos.php?success=3");
} else {
    header("Location: ../contratos.php?error=3");
}

$stmt->close();
$conn->close();
exit();
