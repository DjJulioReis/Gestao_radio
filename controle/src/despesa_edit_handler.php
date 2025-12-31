<?php require_once '../init.php';
// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once 'upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data_vencimento = $_POST['data_vencimento'];
    $tipo = $_POST['tipo'];
    $pago = $_POST['pago'];
    $observacao = $_POST['observacao'] ?? '';

    // Inicia a query e os parâmetros
    $query = "UPDATE despesas SET descricao = ?, valor = ?, data_vencimento = ?, tipo = ?, pago = ?, observacao = ?";
    $params = ["sdssis", $descricao, $valor, $data_vencimento, $tipo, $pago, $observacao];

    // Lida com o upload do recibo
    if (isset($_FILES['recibo']) && $_FILES['recibo']['error'] == UPLOAD_ERR_OK) {
        // Busca o recibo antigo para excluí-lo
        $stmt_old = $conn->prepare("SELECT recibo_path FROM despesas WHERE id = ?");
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $old_receipt = $result_old->fetch_assoc();
        $stmt_old->close();

        $upload_result = handle_receipt_upload($_FILES['recibo']);
        if ($upload_result['success']) {
            if ($old_receipt && !empty($old_receipt['recibo_path'])) {
                $old_receipt_path = __DIR__ . "/../" . $old_receipt['recibo_path'];
                if (file_exists($old_receipt_path)) {
                    unlink($old_receipt_path);
                }
            }

            $recibo_path = $upload_result['filepath'];
            $query .= ", recibo_path = ?";
            $params[0] .= "s";
            $params[] = $recibo_path;
        } else {
            // Tratar erro no upload e notificar o usuário
            header("Location: ../despesa_edit.php?id=$id&error=" . urlencode($upload_result['error']));
            exit();
        }
    }

    $query .= " WHERE id = ?";
    $params[0] .= "i";
    $params[] = $id;

    $stmt = $conn->prepare($query);
    // Usa o call_user_func_array para bind_param dinâmico
    call_user_func_array([$stmt, 'bind_param'], array_merge([$params[0]], array_slice($params, 1)));

    if ($stmt->execute()) {
        require_once 'log_helper.php';
        log_action($_SESSION['user_id'], 'update', 'despesa', $id);
        header("Location: ../despesas.php?success=2");
    } else {
        header("Location: ../despesas.php?error=2");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../despesas.php");
}
exit();
