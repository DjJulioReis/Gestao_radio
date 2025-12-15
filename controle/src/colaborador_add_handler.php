<?php
require_once '../init.php';

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $funcao = $_POST['funcao'];

    $conn->begin_transaction();

    try {
        // Inserir na tabela 'colaboradores'
        $stmt = $conn->prepare("INSERT INTO colaboradores (nome, email, telefone, funcao) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $telefone, $funcao);
        $stmt->execute();

        $colaborador_id = $conn->insert_id; // Pega o ID do colaborador recém-criado

        // Se a função for de sócio, criar a entrada correspondente na tabela 'socios'
        if ($funcao === 'socio' || $funcao === 'socio_locutor') {
            $stmt_socio = $conn->prepare("INSERT INTO socios (colaborador_id, reinvestir_comissao, saldo_investido) VALUES (?, 1, 0.00)");
            $stmt_socio->bind_param("i", $colaborador_id);
            $stmt_socio->execute();
            $stmt_socio->close();
        }

        $conn->commit();
        $_SESSION['success_message'] = "Colaborador adicionado com sucesso!";
        header("Location: ../colaboradores.php");

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Erro ao adicionar colaborador: " . $e->getMessage();
        header("Location: ../colaboradores.php");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../colaborador_add.php");
}
exit();
