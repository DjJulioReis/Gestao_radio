<?php
require_once 'init.php';
$page_title = "Gestão de Investimentos de Sócio";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$socio_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$socio_id) {
    header("Location: socios.php");
    exit();
}

// Buscar dados do sócio
$stmt_socio = $conn->prepare("SELECT c.nome, s.saldo_investido FROM colaboradores c JOIN socios s ON c.id = s.colaborador_id WHERE c.id = ?");
$stmt_socio->bind_param("i", $socio_id);
$stmt_socio->execute();
$result_socio = $stmt_socio->get_result();
if ($result_socio->num_rows === 0) {
    $_SESSION['error_message'] = "Sócio não encontrado.";
    header("Location: socios.php");
    exit();
}
$socio = $result_socio->fetch_assoc();
$stmt_socio->close();

// Buscar histórico de investimentos
$sql_history = "SELECT tipo, valor, data, descricao FROM investimentos_socios WHERE socio_id = ? ORDER BY data DESC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $socio_id);
$stmt_history->execute();
$history = $stmt_history->get_result();

?>

<h1><?php echo $page_title . ': ' . htmlspecialchars($socio['nome']); ?></h1>
<a href="socios.php">Voltar para a Gestão de Sócios</a>

<div class="summary">
    <p><strong>Saldo Investido Atual:</strong> <span style="font-weight: bold; color: <?php echo $socio['saldo_investido'] >= 0 ? 'blue' : 'red'; ?>;">R$ <?php echo number_format($socio['saldo_investido'], 2, ',', '.'); ?></span></p>
</div>

<hr>

<h2>Adicionar Nova Transação</h2>
<form action="src/investimentos_socios_handler.php" method="post" class="form-container">
    <input type="hidden" name="socio_id" value="<?php echo $socio_id; ?>">
    <div class="form-group">
        <label for="tipo">Tipo:</label>
        <select name="tipo" id="tipo" required>
            <option value="investimento">Investimento (Entrada)</option>
            <option value="retirada">Retirada (Saída)</option>
        </select>
    </div>
    <div class="form-group">
        <label for="valor">Valor (R$):</label>
        <input type="number" step="0.01" name="valor" id="valor" required>
    </div>
    <div class="form-group">
        <label for="data">Data:</label>
        <input type="date" name="data" id="data" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    <div class="form-group">
        <label for="descricao">Descrição:</label>
        <input type="text" name="descricao" id="descricao" maxlength="255">
    </div>
    <button type="submit">Adicionar Transação</button>
</form>

<hr>

<h2>Histórico de Transações</h2>
<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Tipo</th>
            <th>Descrição</th>
            <th>Valor (R$)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($history->num_rows > 0): ?>
            <?php while($row = $history->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("d/m/Y", strtotime($row['data'])); ?></td>
                    <td style="color: <?php echo $row['tipo'] === 'investimento' ? 'green' : 'red'; ?>;"><?php echo ucfirst($row['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                    <td><?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Nenhuma transação registrada.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt_history->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>