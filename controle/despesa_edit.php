<?php
require_once 'init.php';
$page_title = "Editar Despesa";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM despesas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: despesas.php");
    exit();
}
$despesa = $result->fetch_assoc();
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/despesa_edit_handler.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $despesa['id']; ?>">
    <div class="form-group">
        <label for="descricao">Descrição</label>
        <input type="text" name="descricao" id="descricao" value="<?php echo htmlspecialchars($despesa['descricao']); ?>" required>
    </div>
    <div class="form-group">
        <label for="valor">Valor (R$)</label>
        <input type="number" step="0.01" name="valor" id="valor" value="<?php echo htmlspecialchars($despesa['valor']); ?>" required>
    </div>
    <div class="form-group">
        <label for="data_vencimento">Data de Vencimento</label>
        <input type="date" name="data_vencimento" id="data_vencimento" value="<?php echo htmlspecialchars($despesa['data_vencimento']); ?>" required>
    </div>
    <div class="form-group">
        <label for="tipo">Tipo de Despesa</label>
        <select name="tipo" id="tipo" required>
            <option value="normal" <?php echo ($despesa['tipo'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
            <option value="fixa" <?php echo ($despesa['tipo'] == 'fixa') ? 'selected' : ''; ?>>Fixa</option>
        </select>
    </div>
    <div class="form-group">
        <label for="observacao">Observação</label>
        <textarea name="observacao" id="observacao" rows="3"><?php echo htmlspecialchars($despesa['observacao'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="recibo">Novo Recibo (opcional)</label>
        <input type="file" name="recibo" id="recibo">
        <?php if (!empty($despesa['recibo_path'])): ?>
            <p>Recibo Atual: <a href="<?php echo htmlspecialchars($despesa['recibo_path']); ?>" target="_blank">Visualizar</a></p>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="pago">Status</label>
        <select name="pago" id="pago" required>
            <option value="0" <?php echo ($despesa['pago'] == 0) ? 'selected' : ''; ?>>Pendente</option>
            <option value="1" <?php echo ($despesa['pago'] == 1) ? 'selected' : ''; ?>>Pago</option>
        </select>
    </div>
    <button type="submit">Salvar Alterações</button>
    <a href="despesas.php" class="cancel-link">Cancelar</a>
</form>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
