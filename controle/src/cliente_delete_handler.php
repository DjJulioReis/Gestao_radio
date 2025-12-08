<?php
require_once '../init.php';

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Verifica se ID foi passado
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header("Location: ../clientes.php");
    exit();
}


    $cliente_id = intval($_GET['id']);

    // Deletar cobranças
    $stmt = $conn->prepare("DELETE FROM cobrancas WHERE cliente_id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stmt->close();

    // Deletar contratos
    $stmt = $conn->prepare("DELETE FROM contratos WHERE cliente_id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stmt->close();

        // Deletar locutor
    $stmt = $conn->prepare("DELETE FROM clientes_locutores WHERE cliente_id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stmt->close();

    // Deletar cliente
    $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../clientes.php?success=3");
    exit();
