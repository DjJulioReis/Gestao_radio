<?php
require_once 'init.php';
$page_title = "Adicionar Despesa";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/despesa_add_handler.php" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="descricao">Descrição</label>
        <input type="text" name="descricao" id="descricao" required>
    </div>
    <div class="form-group">
        <label for="valor">Valor (R$)</label>
        <input type="number" step="0.01" name="valor" id="valor" required>
    </div>
    <div class="form-group">
        <label for="data_vencimento">Data de Vencimento</label>
        <input type="date" name="data_vencimento" id="data_vencimento" required>
    </div>
    <div class="form-group">
        <label for="tipo">Tipo de Despesa</label>
        <select name="tipo" id="tipo" required>
            <option value="normal">Normal</option>
            <option value="fixa">Fixa</option>
        </select>
    </div>
    <div class="form-group">
        <label for="observacao">Observação</label>
        <textarea name="observacao" id="observacao" rows="3"></textarea>
    </div>
    <div class="form-group">
        <label for="recibo">Recibo (PDF, JPG, PNG)</label>
        <input type="file" name="recibo" id="recibo">
    </div>
    <button type="submit">Salvar</button>
    <a href="despesas.php" class="cancel-link">Cancelar</a>
</form>

<?php
require_once  'templates/footer.php';
?>
