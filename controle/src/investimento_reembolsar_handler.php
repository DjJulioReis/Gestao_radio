<?php
require_once '../init.php';

if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$investimento_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$investimento_id) {
    header("Location: ../investimentos_socios.php?error=id_invalido");
    exit();
}

$conn->begin_transaction();

try {
    // 1. Busca o investimento
    $stmt = $conn->prepare("SELECT * FROM investimentos_socios WHERE id = ?");
    $stmt->bind_param("i", $investimento_id);
    $stmt->execute();
    $investimento = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$investimento || $investimento['reembolsado']) {
        throw new Exception("Investimento não encontrado ou já reembolsado.");
    }

    // 2. Marca como reembolsado
    $stmt = $conn->prepare("UPDATE investimentos_socios SET reembolsado = 1 WHERE id = ?");
    $stmt->bind_param("i", $investimento_id);
    $stmt->execute();
    $stmt->close();

    // 3. Deduz do saldo do sócio
    $stmt = $conn->prepare("UPDATE locutores SET saldo_investido = saldo_investido - ? WHERE id = ?");
    $stmt->bind_param("di", $investimento['valor'], $investimento['locutor_id']);
    $stmt->execute();
    $stmt->close();

    // 4. Registra como despesa
    $descricao_despesa = "Reembolso de investimento para " . $investimento['descricao'];
    $data_despesa = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO despesas (descricao, valor, data_vencimento, tipo, pago) VALUES (?, ?, ?, 'normal', 1)");
    $stmt->bind_param("sds", $descricao_despesa, $investimento['valor'], $data_despesa);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header("Location: ../investimentos_socios.php?success=reembolsado");

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao reembolsar investimento: " . $e->getMessage());
    header("Location: ../investimentos_socios.php?error=db_error");
}

$conn->close();
exit();
