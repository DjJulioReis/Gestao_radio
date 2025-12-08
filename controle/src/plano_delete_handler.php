<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../planos.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM planos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../planos.php?success=3");
} else {
    // A falha pode ocorrer por causa de restrições de chave estrangeira (contratos)
    header("Location: ../planos.php?error=3");
}

$stmt->close();
$conn->close();
exit();
