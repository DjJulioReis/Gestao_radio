<?php require_once __DIR__ . '/../init.php'; require_once PROJECT_ROOT . '/src/db_connect.php'; ?>

// Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $insercoes_mes = $_POST['insercoes_mes'];

    $stmt = $conn->prepare("UPDATE planos SET nome = ?, descricao = ?, preco = ?, insercoes_mes = ? WHERE id = ?");
    $stmt->bind_param("ssdii", $nome, $descricao, $preco, $insercoes_mes, $id);

    if ($stmt->execute()) {
        header("Location: ../planos.php?success=2");
    } else {
        header("Location: ../planos.php?error=2");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../planos.php");
}
exit();
