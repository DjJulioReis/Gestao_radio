<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $plano_id = $_POST['plano_id'];
    $tipo_anuncio_id = $_POST['tipo_anuncio_id'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Validação mínima no servidor
    $data_inicio_obj = new DateTime($data_inicio);
    $data_fim_obj = new DateTime($data_fim);
    $intervalo = $data_inicio_obj->diff($data_fim_obj);
    $meses = $intervalo->y * 12 + $intervalo->m;

    if ($meses < 3) {
        // Redireciona com erro se o contrato for menor que 3 meses
        header("Location: ../contrato_add.php?error=short_duration");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO contratos (cliente_id, plano_id, tipo_anuncio_id, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $cliente_id, $plano_id, $tipo_anuncio_id, $data_inicio, $data_fim);

    if ($stmt->execute()) {
        header("Location: ../contratos.php?success=1");
    } else {
        header("Location: ../contratos.php?error=1");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../contrato_add.php");
}
exit();
