<?php
require_once 'init.php';
$page_title = 'Editar Contrato';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$contrato_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$contrato_id) {
    header("Location: contratos.php?error=id_invalido");
    exit();
}

// Buscar dados do contrato
$stmt = $conn->prepare("SELECT * FROM contratos WHERE id = ?");
$stmt->bind_param("i", $contrato_id);
$stmt->execute();
$result = $stmt->get_result();
$contrato = $result->fetch_assoc();
$stmt->close();

if (!$contrato) {
    header("Location: contratos.php?error=nao_encontrado");
    exit();
}

// Buscar clientes e planos para os dropdowns
$clientes = $conn->query("SELECT id, empresa FROM clientes ORDER BY empresa");
$planos = $conn->query("SELECT id, nome FROM planos ORDER BY nome");
?>

<h1><?php echo $page_title; ?></h1>
<a href="contratos.php">Voltar para a Lista</a>

<form action="src/contrato_edit_handler.php" method="post">
    <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">

    <div class="form-group">
        <label for="cliente_id">Cliente</label>
        <select name="cliente_id" id="cliente_id" required>
            <?php while ($cliente = $clientes->fetch_assoc()): ?>
                <option value="<?php echo $cliente['id']; ?>" <?php echo ($cliente['id'] == $contrato['cliente_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cliente['empresa']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="identificacao">Identificação do Contrato (Opcional)</label>
        <input type="text" name="identificacao" id="identificacao" value="<?php echo htmlspecialchars($contrato['identificacao'] ?? ''); ?>" placeholder="Ex: Contrato de Natal, Pacote de Férias">
    </div>

    <div class="form-group">
        <label for="plano_id">Plano</label>
        <select name="plano_id" id="plano_id" required>
            <?php while ($plano = $planos->fetch_assoc()): ?>
                <option value="<?php echo $plano['id']; ?>" <?php echo ($plano['id'] == $contrato['plano_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($plano['nome']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="data_inicio">Data de Início</label>
        <input type="date" name="data_inicio" id="data_inicio" value="<?php echo $contrato['data_inicio']; ?>" required>
    </div>

    <div class="form-group">
        <label for="data_fim">Data de Fim</label>
        <input type="date" name="data_fim" id="data_fim" value="<?php echo $contrato['data_fim']; ?>" required>
    </div>

    <button type="submit">Salvar Alterações</button>
</form>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
