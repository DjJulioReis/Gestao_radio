<?php
require_once __DIR__ . '/../init.php';

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: ../colaboradores.php");
    exit();
}

// O schema do banco de dados foi configurado com ON DELETE CASCADE para as tabelas:
// - cliente_colaboradores (colaborador_id)
// - socios (colaborador_id)
// - investimentos_socios (socio_id)
// Isso significa que, ao deletar um colaborador, os registros associados nessas tabelas serão automaticamente removidos pelo banco de dados.
// Portanto, não precisamos de uma transação explícita aqui para remover de várias tabelas.

try {
    $stmt = $conn->prepare("DELETE FROM colaboradores WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Colaborador excluído com sucesso.";
    } else {
        throw new Exception($stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    // Se houver uma restrição de chave estrangeira que NÃO seja ON DELETE CASCADE (ex: tabela de pagamentos), a exclusão falhará.
    // Nesse caso, capturamos a exceção e mostramos uma mensagem de erro amigável.
    $_SESSION['error_message'] = "Não foi possível excluir o colaborador. Verifique se ele não possui registros de pagamentos ou outras dependências não resolvidas. Erro: " . $e->getMessage();
}

$conn->close();
header("Location: ../colaboradores.php");
exit();
