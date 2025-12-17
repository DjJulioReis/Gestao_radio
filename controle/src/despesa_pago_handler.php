<?php require_once '../init.php'; 

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

// Apenas atualiza o status para pago
$stmt = $conn->prepare("UPDATE despesas SET pago = 1 WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../despesas.php?success=4"); // 4 for payment success
} else {
    header("Location: ../despesas.php?error=4"); // 4 for payment error
}

$stmt->close();
$conn->close();
exit();
