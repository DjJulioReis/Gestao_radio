<?php
require_once 'init.php';
$page_title = "Relatório de Contas a Receber (Cobranças)";
require_once 'templates/header.php';

$status_filtro = $_GET['status'] ?? 'todos'; // 'todos', 'pagas', 'pendentes'
$mes_filtro = $_GET['mes'] ?? date('Y-m');

$where_clauses = [];
$params = [];
$types = '';

if ($status_filtro === 'pagas') {
    $where_clauses[] = "c.pago = 1";
} elseif ($status_filtro === 'pendentes') {
    $where_clauses[] = "c.pago = 0";
}

if (!empty($mes_filtro)) {
    $where_clauses[] = "c.referencia = ?";
    $params[] = $mes_filtro;
    $types .= 's';
}

$sql = "
    SELECT
        c.id,
        cl.empresa AS cliente,
        p.nome AS plano,
        c.valor,
        c.referencia,
        c.pago,
        c.data_pagamento
    FROM
        cobrancas c
    JOIN
        clientes cl ON c.cliente_id = cl.id
    JOIN
        planos p ON c.plano_id = p.id
";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY c.referencia DESC, cl.empresa ASC";

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
        <label for="mes">Mês de Referência:</label>
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
            <th>Cliente</th>
            <th>Plano</th>
            <th>Valor (R$)</th>
            <th>Referência</th>
            <th>Status</th>
            <th>Data de Pagamento</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($row['plano']); ?></td>
                    <td><?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($row['referencia']); ?></td>
                    <td class="<?php echo $row['pago'] ? 'pago-sim' : 'pago-nao'; ?>"><?php echo $row['pago'] ? 'Pago' : 'Pendente'; ?></td>
                    <td><?php echo $row['data_pagamento'] ? date("d/m/Y", strtotime($row['data_pagamento'])) : 'N/A'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">Nenhuma cobrança encontrada para os filtros selecionados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer.php';
?>
