<?php
require_once '../init.php';

// Apenas administradores podem adicionar contratos
if ($_SESSION['user_level'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php");
    exit();
}

// Validação dos dados recebidos
$cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
$plano_id = filter_input(INPUT_POST, 'plano_id', FILTER_VALIDATE_INT);
$identificacao = trim(filter_input(INPUT_POST, 'identificacao', FILTER_SANITIZE_STRING));
$valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
$data_inicio = $_POST['data_inicio']; // Adicionar validação de data se necessário
$data_fim = $_POST['data_fim'];       // Adicionar validação de data se necessário

if (!$cliente_id || !$plano_id || !$valor || empty($data_inicio) || empty($data_fim)) {
    // Redireciona de volta com erro se dados forem inválidos
    $_SESSION['error_message'] = "Dados inválidos. Verifique todos os campos.";
    header("Location: ../contrato_add.php");
    exit();
}

// Prepara e executa a query de inserção
$stmt = $conn->prepare(
    "INSERT INTO contratos (cliente_id, plano_id, identificacao, valor, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("iisdss", $cliente_id, $plano_id, $identificacao, $valor, $data_inicio, $data_fim);

if ($stmt->execute()) {
    $contrato_id = $stmt->insert_id;

    // Lógica para gerar cobranças mensais
    $inicio = new DateTime($data_inicio);
    $fim = new DateTime($data_fim);
    // Adiciona um dia ao fim para incluir o último mês no loop
    $fim->modify('+1 day');
    $intervalo = new DateInterval('P1M');
    $periodo = new DatePeriod($inicio, $intervalo, $fim);

    $stmt_cobranca = $conn->prepare(
        "INSERT INTO cobrancas (contrato_id, cliente_id, plano_id, valor, referencia) VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($periodo as $data) {
        $referencia = $data->format('Y-m');
        $stmt_cobranca->bind_param("iiids", $contrato_id, $cliente_id, $plano_id, $valor_plano, $referencia);
        $stmt_cobranca->execute();
    }
    $stmt_cobranca->close();

    // Redireciona para a lista de contratos com sucesso
    header("Location: ../contratos.php?success=1");
    exit();
} else {
    // Log do erro e redirecionamento
    error_log("Erro ao adicionar contrato: " . $stmt->error);
    header("Location: ../contrato_add.php?error=db_error");
}

$stmt->close();
$conn->close();
exit();
