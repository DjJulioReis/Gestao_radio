<?php
require_once 'init.php';
$page_title = "Editar Colaborador";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM colaboradores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: colaboradores.php");
    exit();
}
$colaborador = $result->fetch_assoc();
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/colaborador_edit_handler.php" method="post">
    <input type="hidden" name="id" value="<?php echo $colaborador['id']; ?>">
    <div class="form-group">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($colaborador['nome']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($colaborador['email']); ?>" required>
    </div>
    <div class="form-group">
        <label for="telefone">Telefone</label>
        <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($colaborador['telefone']); ?>" required>
    </div>
    <div class="form-group">
        <label for="funcao">Função</label>
        <select name="funcao" id="funcao" required>
            <option value="locutor" <?php echo ($colaborador['funcao'] == 'locutor') ? 'selected' : ''; ?>>Locutor</option>
            <option value="socio" <?php echo ($colaborador['funcao'] == 'socio') ? 'selected' : ''; ?>>Sócio</option>
            <option value="socio_locutor" <?php echo ($colaborador['funcao'] == 'socio_locutor') ? 'selected' : ''; ?>>Sócio e Locutor</option>
            <option value="parceiro" <?php echo ($colaborador['funcao'] == 'parceiro') ? 'selected' : ''; ?>>Parceiro</option>
        </select>
    </div>
    <button type="submit">Salvar Alterações</button>
    <a href="colaboradores.php" class="cancel-link">Cancelar</a>
</form>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
