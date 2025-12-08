<?php require_once '../init.php';
// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data_vencimento = $_POST['data_vencimento'];
    $tipo = $_POST['tipo'];
    $pago = $_POST['pago'];

    $stmt = $conn->prepare("UPDATE despesas SET descricao = ?, valor = ?, data_vencimento = ?, tipo = ?, pago = ? WHERE id = ?");
    $stmt->bind_param("sdssii", $descricao, $valor, $data_vencimento, $tipo, $pago, $id);

    if ($stmt->execute()) {
        header("Location: ../despesas.php?success=2");
    } else {
        header("Location: ../despesas.php?error=2");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../despesas.php");
}
exit();
