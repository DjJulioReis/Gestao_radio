<?php require_once'../init.php'; 

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    $stmt = $conn->prepare("INSERT INTO locutores (nome, email, telefone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $email, $telefone);

    if ($stmt->execute()) {
        header("Location: ../locutores.php?success=1");
    } else {
        header("Location: ../locutores.php?error=1");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../locutor_add.php");
}
exit();
