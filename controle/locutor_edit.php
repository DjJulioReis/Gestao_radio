<?php
require_once 'init.php';
$page_title = "Editar Locutor";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM locutores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: locutores.php");
    exit();
}
$locutor = $result->fetch_assoc();
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/locutor_edit_handler.php" method="post">
    <input type="hidden" name="id" value="<?php echo $locutor['id']; ?>">
    <div class="form-group">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($locutor['nome']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($locutor['email']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Telefone</label>
        <input type="telefone" name="telefone" id="telefone" value="<?php echo htmlspecialchars($locutor['telefone']); ?>" required>
    </div>
    <button type="submit">Salvar Alterações</button>
    <a href="locutores.php" class="cancel-link">Cancelar</a>
</form>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
