<?php require_once  '../init.php'; 

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data_vencimento = $_POST['data_vencimento'];
    $tipo = $_POST['tipo'];

    $stmt = $conn->prepare("INSERT INTO despesas (descricao, valor, data_vencimento, tipo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $descricao, $valor, $data_vencimento, $tipo);

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
