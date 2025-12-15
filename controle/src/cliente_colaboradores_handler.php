<?php
require_once '../init.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $associacoes = $_POST['associacao'] ?? [];

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // 1. Limpar todas as associações existentes.
        // Em um cenário mais complexo, você poderia querer atualizar apenas o que mudou.
        // Para este caso, limpar e recriar é mais simples.
        $conn->query("DELETE FROM cliente_colaboradores");

        // 2. Inserir as novas associações
        $sql = "INSERT INTO cliente_colaboradores (cliente_id, colaborador_id, percentual_comissao) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($associacoes as $cliente_id => $dados) {
            $colaborador_id = filter_var($dados['colaborador_id'], FILTER_VALIDATE_INT);
            $percentual_comissao = filter_var($dados['percentual_comissao'], FILTER_VALIDATE_FLOAT);

            // Apenas insere se um colaborador válido foi selecionado
            if ($colaborador_id) {
                $stmt->bind_param("iid", $cliente_id, $colaborador_id, $percentual_comissao);
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao inserir associação: " . $stmt->error);
                }
            }
        }

        // Commit da transação
        $conn->commit();
        $_SESSION['success_message'] = "Associações salvas com sucesso!";

    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        $_SESSION['error_message'] = "Erro ao salvar associações: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();

    header("Location: ../cliente_colaboradores.php");
    exit();
}
?>