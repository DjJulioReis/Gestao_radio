<?php 
require_once '../init.php'; 

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $insercoes_mes = $_POST['insercoes_mes'];

    $stmt = $conn->prepare("INSERT INTO planos (nome, descricao, preco, insercoes_mes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $nome, $descricao, $preco, $insercoes_mes);

    if ($stmt->execute()) {
        header("Location: ../planos.php?success=1");
    } else {
        header("Location: ../planos.php?error=1");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../plano_add.php");
}
exit();
