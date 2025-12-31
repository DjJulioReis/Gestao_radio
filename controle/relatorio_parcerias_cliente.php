<?php
require_once 'init.php';
$page_title = "Relatório de Parcerias por Cliente";
require_once 'templates/header.php';

$sql = "
    SELECT
        c.empresa AS cliente,
        p.nome AS plano,
        ct.data_inicio,
        ct.data_fim,
        p.preco AS valor_plano
    FROM
        clientes c
    JOIN
        contratos ct ON c.id = ct.cliente_id
    JOIN
        planos p ON ct.plano_id = p.id
    ORDER BY
        c.empresa, ct.data_inicio DESC
";

$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<table>
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Plano</th>
            <th>Data de Início</th>
            <th>Data de Fim</th>
            <th>Valor do Plano (R$)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($row['plano']); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_inicio'])); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_fim'])); ?></td>
                    <td><?php echo number_format($row['valor_plano'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhuma parceria encontrada.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
