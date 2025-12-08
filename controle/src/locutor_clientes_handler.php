<?php require_once  '../init.php'; 

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();

    try {
        // 1. Limpar todas as associações existentes
        $conn->query("DELETE FROM clientes_locutores");

        // 2. Inserir as novas associações
        if (isset($_POST['associacao'])) {
            $stmt = $conn->prepare("INSERT INTO clientes_locutores (cliente_id, locutor_id) VALUES (?, ?)");
            
            foreach ($_POST['associacao'] as $cliente_id => $locutor_id) {
                // Apenas insere se um locutor for selecionado
                if (!empty($locutor_id)) {
                    $stmt->bind_param("ii", $cliente_id, $locutor_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        // 3. Confirmar a transação
        $conn->commit();
        header("Location: ../locutor_clientes.php?success=1");

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        header("Location: ../locutor_clientes.php?error=1");
    }

    $conn->close();
} else {
    header("Location: ../locutor_clientes.php");
}
exit();
