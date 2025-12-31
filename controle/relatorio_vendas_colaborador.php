<?php
require_once 'init.php';
$page_title = "Relatório de Vendas por Locutor";
require_once 'templates/header.php';

$sql = "
    SELECT
        l.nome AS locutor,
        COUNT(c.id) AS total_vendas,
        SUM(c.valor) AS valor_total_vendas
    FROM
        locutores l
    JOIN
        clientes_locutores cl ON l.id = cl.locutor_id
    JOIN
        cobrancas c ON cl.cliente_id = c.cliente_id
    WHERE
        c.pago = 1
    GROUP BY
        l.nome
    ORDER BY
        valor_total_vendas DESC
";

$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<table>
    <thead>
        <tr>
            <th>Locutor</th>
            <th>Total de Vendas (Pagas)</th>
            <th>Valor Total (R$)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['locutor']); ?></td>
                    <td><?php echo $row['total_vendas']; ?></td>
                    <td><?php echo number_format($row['valor_total_vendas'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">Nenhuma venda encontrada.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
