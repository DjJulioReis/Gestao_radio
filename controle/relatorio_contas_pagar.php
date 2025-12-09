<?php
require_once 'init.php';
$page_title = "Relatório de Contas a Pagar (Despesas)";
require_once 'templates/header.php';

$status_filtro = $_GET['status'] ?? 'todos'; // 'todos', 'pagas', 'pendentes'
$mes_filtro = $_GET['mes'] ?? date('Y-m');

$where_clauses = [];
$params = [];
$types = '';

if ($status_filtro === 'pagas') {
    $where_clauses[] = "pago = 1";
} elseif ($status_filtro === 'pendentes') {
    $where_clauses[] = "pago = 0";
}

if (!empty($mes_filtro)) {
    $where_clauses[] = "DATE_FORMAT(data_vencimento, '%Y-%m') = ?";
    $params[] = $mes_filtro;
    $types .= 's';
}

$sql = "SELECT id, descricao, valor, data_vencimento, tipo, pago, observacao, recibo_path FROM despesas";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY data_vencimento DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<h1><?php echo $page_title; ?></h1>

<form method="GET" action="">
    <div class="form-group">
        <label for="mes">Mês:</label>
        <input type="month" id="mes" name="mes" value="<?php echo htmlspecialchars($mes_filtro); ?>">
    </div>
    <div class="form-group">
        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="todos" <?php echo ($status_filtro === 'todos') ? 'selected' : ''; ?>>Todos</option>
            <option value="pagas" <?php echo ($status_filtro === 'pagas') ? 'selected' : ''; ?>>Pagas</option>
            <option value="pendentes" <?php echo ($status_filtro === 'pendentes') ? 'selected' : ''; ?>>Pendentes</option>
        </select>
    </div>
    <button type="submit">Filtrar</button>
</form>

<a href="dashboard.php">Voltar para o Dashboard</a>

<table>
    <thead>
        <tr>
            <th>Descrição</th>
            <th>Valor (R$)</th>
            <th>Vencimento</th>
            <th>Status</th>
            <th>Observação</th>
            <th>Recibo</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                    <td><?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_vencimento'])); ?></td>
                    <td class="<?php echo $row['pago'] ? 'pago-sim' : 'pago-nao'; ?>"><?php echo $row['pago'] ? 'Pago' : 'Pendente'; ?></td>
                    <td><?php echo htmlspecialchars($row['observacao'] ?? ''); ?></td>
                    <td>
                        <?php if (!empty($row['recibo_path'])): ?>
                            <a href="<?php echo htmlspecialchars($row['recibo_path']); ?>" target="_blank">Ver</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">Nenhuma despesa encontrada para os filtros selecionados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer.php';
?>
