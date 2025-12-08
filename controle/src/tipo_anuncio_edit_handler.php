<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];

    $stmt = $conn->prepare("UPDATE tipos_anuncio SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $nome, $id);

    if ($stmt->execute()) {
        header("Location: ../tipos_anuncio.php?success=2");
    } else {
        header("Location: ../tipos_anuncio.php?error=2");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../tipos_anuncio.php");
}
exit();
