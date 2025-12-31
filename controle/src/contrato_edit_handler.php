<?php
require_once '../init.php';

// Apenas administradores e requisições POST
if ($_SESSION['user_level'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php");
    exit();
}

// Validação dos dados
$contrato_id = filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT);
$cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
$plano_id = filter_input(INPUT_POST, 'plano_id', FILTER_VALIDATE_INT);
$identificacao = trim(filter_input(INPUT_POST, 'identificacao', FILTER_SANITIZE_STRING));
$valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
$data_inicio = $_POST['data_inicio'];
$data_fim = $_POST['data_fim'];

if (!$contrato_id || !$cliente_id || !$plano_id || !$valor || empty($data_inicio) || empty($data_fim)) {
    $_SESSION['error_message'] = "Dados inválidos. Verifique todos os campos.";
    header("Location: ../contrato_edit.php?id={$contrato_id}");
    exit();
}

// Inicia a transação
$conn->begin_transaction();

try {
    // 1. Atualiza o contrato
    $stmt = $conn->prepare("UPDATE contratos SET cliente_id = ?, plano_id = ?, identificacao = ?, valor = ?, data_inicio = ?, data_fim = ? WHERE id = ?");
    $stmt->bind_param("iisdssi", $cliente_id, $plano_id, $identificacao, $valor, $data_inicio, $data_fim, $contrato_id);
    $stmt->execute();
    $stmt->close();

    // 2. Exclui cobranças futuras (não pagas)
    $stmt_del = $conn->prepare("DELETE FROM cobrancas WHERE contrato_id = ? AND pago = 0");
    $stmt_del->bind_param("i", $contrato_id);
    $stmt_del->execute();
    $stmt_del->close();

    // 3. Gera novas cobranças
    $inicio = new DateTime($data_inicio);
    $fim = new DateTime($data_fim);
    $fim->modify('+1 day');
    $intervalo = new DateInterval('P1M');
    $periodo = new DatePeriod($inicio, $intervalo, $fim);

    $stmt_cobranca = $conn->prepare("INSERT INTO cobrancas (contrato_id, cliente_id, plano_id, valor, referencia) VALUES (?, ?, ?, ?, ?)");

    foreach ($periodo as $data) {
        $referencia = $data->format('Y-m');

        // Verifica se já existe uma cobrança paga para este mês antes de inserir
        $check_stmt = $conn->prepare("SELECT id FROM cobrancas WHERE contrato_id = ? AND referencia = ? AND pago = 1");
        $check_stmt->bind_param("is", $contrato_id, $referencia);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows == 0) {
            $stmt_cobranca->bind_param("iiids", $contrato_id, $cliente_id, $plano_id, $valor, $referencia);
            $stmt_cobranca->execute();
        }
        $check_stmt->close();
    }
    $stmt_cobranca->close();

    // Comita a transação
    $conn->commit();
    header("Location: ../contratos.php?success=editado");

} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    error_log("Erro ao editar contrato: " . $exception->getMessage());
    header("Location: ../contrato_edit.php?id={$contrato_id}&error=db_error");
}

$conn->close();
exit();
