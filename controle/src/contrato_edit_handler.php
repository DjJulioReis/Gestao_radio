<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $cliente_id = $_POST['cliente_id'];
    $plano_id = $_POST['plano_id'];
    $tipo_anuncio_id = $_POST['tipo_anuncio_id'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Validação
    $data_inicio_obj = new DateTime($data_inicio);
    $data_fim_obj = new DateTime($data_fim);
    $intervalo = $data_inicio_obj->diff($data_fim_obj);
    $meses = $intervalo->y * 12 + $intervalo->m;

    if ($meses < 3) {
        header("Location: ../contrato_edit.php?id=$id&error=short_duration");
        exit();
    }

    $stmt = $conn->prepare("UPDATE contratos SET cliente_id = ?, plano_id = ?, tipo_anuncio_id = ?, data_inicio = ?, data_fim = ? WHERE id = ?");
    $stmt->bind_param("iiissi", $cliente_id, $plano_id, $tipo_anuncio_id, $data_inicio, $data_fim, $id);

    if ($stmt->execute()) {
        header("Location: ../contratos.php?success=2");
    } else {
        header("Location: ../contratos.php?error=2");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../contratos.php");
}
exit();
