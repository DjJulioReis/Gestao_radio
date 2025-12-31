<?php
require_once __DIR__ . '/../init.php';

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $funcao = $_POST['funcao'];

    if (!$id) {
        header("Location: ../colaboradores.php");
        exit();
    }

    $conn->begin_transaction();

    try {
        // 1. Obter a função ATUAL do colaborador no banco
        $stmt_old = $conn->prepare("SELECT funcao FROM colaboradores WHERE id = ?");
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $colaborador_antigo = $result_old->fetch_assoc();
        $funcao_antiga = $colaborador_antigo['funcao'];
        $stmt_old->close();

        // 2. Atualizar a tabela 'colaboradores'
        $stmt = $conn->prepare("UPDATE colaboradores SET nome = ?, email = ?, telefone = ?, funcao = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nome, $email, $telefone, $funcao, $id);
        $stmt->execute();
        $stmt->close();

        $funcao_nova_eh_socio = ($funcao === 'socio' || $funcao === 'socio_locutor');
        $funcao_antiga_era_socio = ($funcao_antiga === 'socio' || $funcao_antiga === 'socio_locutor');

        // 3. Lógica para criar ou remover entrada na tabela 'socios'
        if ($funcao_nova_eh_socio && !$funcao_antiga_era_socio) {
            // Se tornou sócio: criar entrada em 'socios'
            $stmt_socio = $conn->prepare("INSERT INTO socios (colaborador_id, reinvestir_comissao, saldo_investido) VALUES (?, 1, 0.00) ON DUPLICATE KEY UPDATE colaborador_id=colaborador_id");
            $stmt_socio->bind_param("i", $id);
            $stmt_socio->execute();
            $stmt_socio->close();
        } elseif (!$funcao_nova_eh_socio && $funcao_antiga_era_socio) {
            // Deixou de ser sócio: remover entrada de 'socios'
            // CUIDADO: Isso pode remover dados financeiros. A lógica de negócio deve confirmar se isso é desejável.
            // Por ora, vamos remover para manter a consistência.
            $stmt_socio = $conn->prepare("DELETE FROM socios WHERE colaborador_id = ?");
            $stmt_socio->bind_param("i", $id);
            $stmt_socio->execute();
            $stmt_socio->close();
        }

        $conn->commit();
        $_SESSION['success_message'] = "Colaborador atualizado com sucesso!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Erro ao atualizar colaborador: " . $e->getMessage();
    }

    $conn->close();
    header("Location: ../colaboradores.php");
    exit();

} else {
    header("Location: ../colaboradores.php");
}
exit();
