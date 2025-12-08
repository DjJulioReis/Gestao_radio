$page_title = "Editar Tipo de Anúncio";
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/src/db_connect.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM tipos_anuncio WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: tipos_anuncio.php");
    exit();
}
$tipo_anuncio = $result->fetch_assoc();
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/tipo_anuncio_edit_handler.php" method="post">
    <input type="hidden" name="id" value="<?php echo $tipo_anuncio['id']; ?>">
    <div class="form-group">
        <label for="nome">Nome do Tipo</label>
        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($tipo_anuncio['nome']); ?>" required>
    </div>
    <button type="submit">Salvar Alterações</button>
    <a href="tipos_anuncio.php" class="cancel-link">Cancelar</a>
</form>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
