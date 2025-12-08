<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];

    $stmt = $conn->prepare("INSERT INTO tipos_anuncio (nome) VALUES (?)");
    $stmt->bind_param("s", $nome);

    if ($stmt->execute()) {
        header("Location: ../tipos_anuncio.php?success=1");
    } else {
        header("Location: ../tipos_anuncio.php?error=1");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../tipo_anuncio_add.php");
}
exit();
