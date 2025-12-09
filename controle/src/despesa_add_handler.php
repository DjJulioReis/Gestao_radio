<?php require_once  '../init.php'; 

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once 'upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data_vencimento = $_POST['data_vencimento'];
    $tipo = $_POST['tipo'];
    $observacao = $_POST['observacao'] ?? '';
    $recibo_path = null;

    if (isset($_FILES['recibo']) && $_FILES['recibo']['error'] == UPLOAD_ERR_OK) {
        $upload_result = handle_receipt_upload($_FILES['recibo']);
        if ($upload_result['success']) {
            $recibo_path = $upload_result['filepath'];
        } else {
            // Tratar erro no upload e notificar o usuário
            header("Location: ../despesa_add.php?error=" . urlencode($upload_result['error']));
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO despesas (descricao, valor, data_vencimento, tipo, observacao, recibo_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssss", $descricao, $valor, $data_vencimento, $tipo, $observacao, $recibo_path);

    if ($stmt->execute()) {
        header("Location: ../despesas.php?success=1");
    } else {
        header("Location: ../despesas.php?error=1");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../despesa_add.php");
}
exit();
