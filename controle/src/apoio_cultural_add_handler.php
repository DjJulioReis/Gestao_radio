<?php
require_once '../init.php';

// Apenas administradores podem adicionar projetos
if ($_SESSION['user_level'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php");
    exit();
}

// Validação simples dos dados recebidos
$nome_projeto = trim($_POST['nome_projeto']);
$descricao = trim($_POST['descricao']);
$meta_arrecadacao = !empty($_POST['meta_arrecadacao']) ? (float)$_POST['meta_arrecadacao'] : null;
$data_inicio = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
$data_fim = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;

if (empty($nome_projeto)) {
    // Tratamento de erro (poderia ser mais robusto)
    header("Location: ../apoio_cultural_add.php?error=nome_vazio");
    exit();
}

// Prepara e executa a query de inserção
$stmt = $conn->prepare(
    "INSERT INTO apoios_culturais (nome_projeto, descricao, meta_arrecadacao, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssdss", $nome_projeto, $descricao, $meta_arrecadacao, $data_inicio, $data_fim);

if ($stmt->execute()) {
    // Redireciona para a lista de projetos com sucesso
    header("Location: ../apoios_culturais.php?success=1");
} else {
    // Redireciona com erro
    error_log("Erro ao salvar projeto cultural: " . $stmt->error);
    header("Location: ../apoio_cultural_add.php?error=db_error");
}

$stmt->close();
$conn->close();
exit();
