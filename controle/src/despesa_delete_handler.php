<?php require_once  '../init.php';

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../despesas.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM despesas WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../despesas.php?success=3");
} else {
    header("Location: ../despesas.php?error=3");
}

$stmt->close();
$conn->close();
exit();
