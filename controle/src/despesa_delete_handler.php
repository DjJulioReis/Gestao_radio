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

// Busca o recibo para excluí-lo
$stmt_select = $conn->prepare("SELECT recibo_path FROM despesas WHERE id = ?");
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$despesa = $result->fetch_assoc();
$stmt_select->close();

if ($despesa && !empty($despesa['recibo_path'])) {
    $receipt_path = __DIR__ . "/../" . $despesa['recibo_path'];
    if (file_exists($receipt_path)) {
        unlink($receipt_path);
    }
}

$stmt_delete = $conn->prepare("DELETE FROM despesas WHERE id = ?");
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    require_once 'log_helper.php';
    log_action($_SESSION['user_id'], 'delete', 'despesa', $id);
    header("Location: ../despesas.php?success=3");
} else {
    header("Location: ../despesas.php?error=3");
}

$stmt->close();
$conn->close();
exit();
