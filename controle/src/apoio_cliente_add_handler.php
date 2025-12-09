<?php
require_once '../init.php';

// Apenas administradores podem adicionar apoiadores
if ($_SESSION['user_level'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php");
    exit();
}

// Validação dos dados recebidos
$apoio_id = filter_input(INPUT_POST, 'apoio_id', FILTER_VALIDATE_INT);
$cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
$valor_doado = filter_input(INPUT_POST, 'valor_doado', FILTER_VALIDATE_FLOAT);
$forma_anuncio = trim($_POST['forma_anuncio']);

if (!$apoio_id || !$cliente_id || $valor_doado === false) {
    // Redireciona de volta para a página do projeto com erro
    header("Location: ../apoio_cultural_view.php?id=" . (int)$apoio_id . "&error=dados_invalidos");
    exit();
}

// Prepara e executa a query de inserção
$stmt = $conn->prepare(
    "INSERT INTO apoios_clientes (apoio_id, cliente_id, valor_doado, forma_anuncio) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("iids", $apoio_id, $cliente_id, $valor_doado, $forma_anuncio);

if ($stmt->execute()) {
    // Redireciona de volta para a página do projeto com sucesso
    header("Location: ../apoio_cultural_view.php?id=" . $apoio_id . "&success=1");
} else {
    // Log do erro e redirecionamento
    error_log("Erro ao adicionar apoiador: " . $stmt->error);
    header("Location: ../apoio_cultural_view.php?id=" . $apoio_id . "&error=db_error");
}

$stmt->close();
$conn->close();
exit();
