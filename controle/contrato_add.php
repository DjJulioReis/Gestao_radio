<?php
require_once 'init.php';
$page_title = "Adicionar Novo Contrato";
require_once 'templates/header.php';

// Apenas administradores podem adicionar contratos
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Busca clientes e planos para os dropdowns
$clientes = $conn->query("SELECT id, empresa FROM clientes WHERE ativo = 1 ORDER BY empresa");
$planos = $conn->query("SELECT id, nome FROM planos ORDER BY nome");
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/contrato_add_handler.php" method="post">
    <div class="form-group">
        <label for="cliente_id">Cliente</label>
        <select name="cliente_id" id="cliente_id" class="form-control" required>
            <option value="">-- Selecione um Cliente --</option>
            <?php while ($cliente = $clientes->fetch_assoc()): ?>
                <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['empresa']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="plano_id">Plano</label>
        <select name="plano_id" id="plano_id" class="form-control" required>
            <option value="">-- Selecione um Plano --</option>
            <?php while ($plano = $planos->fetch_assoc()): ?>
                <option value="<?php echo $plano['id']; ?>"><?php echo htmlspecialchars($plano['nome']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="data_inicio">Data de Início</label>
        <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="data_fim">Data de Fim</label>
        <input type="date" name="data_fim" id="data_fim" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Salvar Contrato</button>
    <a href="contratos.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php
$clientes->close();
$planos->close();
$conn->close();
require_once 'templates/footer.php';
?>
