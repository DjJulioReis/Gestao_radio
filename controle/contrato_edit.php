$page_title = "Editar Contrato";
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/src/db_connect.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM contratos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: contratos.php");
    exit();
}
$contrato = $result->fetch_assoc();

// Buscar dados para os dropdowns
$clientes = $conn->query("SELECT id, empresa FROM clientes ORDER BY empresa");
$planos = $conn->query("SELECT id, nome FROM planos ORDER BY nome");
$tipos_anuncio = $conn->query("SELECT id, nome FROM tipos_anuncio ORDER BY nome");

$min_date = date('Y-m-d');
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/contrato_edit_handler.php" method="post" id="contratoForm">
    <input type="hidden" name="id" value="<?php echo $contrato['id']; ?>">
    <div class="form-group">
        <label for="cliente_id">Cliente</label>
        <select name="cliente_id" id="cliente_id" required>
            <?php while($cliente = $clientes->fetch_assoc()): ?>
                <option value="<?php echo $cliente['id']; ?>" <?php if($cliente['id'] == $contrato['cliente_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cliente['empresa']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="plano_id">Plano</label>
        <select name="plano_id" id="plano_id" required>
            <?php while($plano = $planos->fetch_assoc()): ?>
                <option value="<?php echo $plano['id']; ?>" <?php if($plano['id'] == $contrato['plano_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($plano['nome']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
     <div class="form-group">
        <label for="tipo_anuncio_id">Tipo de Anúncio</label>
        <select name="tipo_anuncio_id" id="tipo_anuncio_id" required>
            <?php while($tipo = $tipos_anuncio->fetch_assoc()): ?>
                <option value="<?php echo $tipo['id']; ?>" <?php if($tipo['id'] == $contrato['tipo_anuncio_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($tipo['nome']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="data_inicio">Data de Início</label>
        <input type="date" name="data_inicio" id="data_inicio" value="<?php echo htmlspecialchars($contrato['data_inicio']); ?>" min="<?php echo $min_date; ?>" required>
    </div>
    <div class="form-group">
        <label for="data_fim">Data de Fim (mínimo 3 meses)</label>
        <input type="date" name="data_fim" id="data_fim" value="<?php echo htmlspecialchars($contrato['data_fim']); ?>" readonly required>
    </div>
    <button type="submit">Salvar Alterações</button>
    <a href="contratos.php" class="cancel-link">Cancelar</a>
</form>

<script>
    document.getElementById('data_inicio').addEventListener('change', function() {
        const dataInicio = new Date(this.value + 'T00:00:00');
        if (!isNaN(dataInicio.getTime())) {
            dataInicio.setMonth(dataInicio.getMonth() + 3);
            const ano = dataInicio.getFullYear();
            const mes = String(dataInicio.getMonth() + 1).padStart(2, '0');
            const dia = String(dataInicio.getDate()).padStart(2, '0');
            document.getElementById('data_fim').value = `${ano}-${mes}-${dia}`;
        }
    });
</script>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
