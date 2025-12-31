<?php
require_once '../init.php';

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['socio_id'])) {
    $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT);
    $tipo = $_POST['tipo'];
    $valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
    $data = filter_input(INPUT_POST, 'data') ?: date('Y-m-d');

    if ($valor > 0 && $socio_id) {
        $conn->begin_transaction();
        try {
            // Insere o registro do investimento
            $sql_insert = "INSERT INTO investimentos_socios (socio_id, tipo, valor, data, descricao) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("isdss", $socio_id, $tipo, $valor, $data, $descricao);
            $stmt_insert->execute();
            $stmt_insert->close();

            // Atualiza o saldo do sócio
            if ($tipo === 'investimento') {
                $sql_update = "UPDATE socios SET saldo_investido = saldo_investido + ? WHERE colaborador_id = ?";
            } elseif ($tipo === 'retirada') {
                $sql_update = "UPDATE socios SET saldo_investido = saldo_investido - ? WHERE colaborador_id = ?";
            }

            if (isset($sql_update)) {
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("di", $valor, $socio_id);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $conn->commit();
            $_SESSION['success_message'] = "Transação registrada com sucesso!";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Erro ao registrar transação: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "O valor deve ser positivo e um sócio deve ser selecionado.";
    }

    header("Location: ../investimentos_socios.php?id=$socio_id");
    exit();
} else {
    header("Location: ../socios.php");
    exit();
}
?>