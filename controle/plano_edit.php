<?php
require_once 'init.php';
$page_title = "Editar Plano";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM planos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: planos.php");
    exit();
}
$plano = $result->fetch_assoc();
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/plano_edit_handler.php" method="post">
    <input type="hidden" name="id" value="<?php echo $plano['id']; ?>">
    <div class="form-group">
        <label for="nome">Nome do Plano</label>
        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($plano['nome']); ?>" required>
    </div>
    <div class="form-group">
        <label for="descricao">Descrição</label>
        <textarea name="descricao" id="descricao" rows="4"><?php echo htmlspecialchars($plano['descricao']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="preco">Preço (R$)</label>
        <input type="number" step="0.01" name="preco" id="preco" value="<?php echo htmlspecialchars($plano['preco']); ?>" required>
    </div>
    <div class="form-group">
        <label for="insercoes_mes">Inserções/Mês</label>
        <input type="number" name="insercoes_mes" id="insercoes_mes" value="<?php echo htmlspecialchars($plano['insercoes_mes']); ?>" required>
    </div>
    <button type="submit">Salvar Alterações</button>
    <a href="planos.php" class="cancel-link">Cancelar</a>
</form>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
