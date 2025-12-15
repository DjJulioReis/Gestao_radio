<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $reinvestir = isset($_POST['reinvestir_comissao']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE locutores SET nome = ?, email = ?, telefone = ?, reinvestir_comissao = ? WHERE id = ?");
    $stmt->bind_param("sssii", $nome, $email, $telefone, $reinvestir, $id);

    if ($stmt->execute()) {
        header("Location: ../locutores.php?success=2");
    } else {
        header("Location: ../locutores.php?error=2");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../locutores.php");
}
exit();
