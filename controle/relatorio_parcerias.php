<?php
require_once 'init.php';
$page_title = "Relatório de Parcerias";
require_once 'templates/header.php';

$sql = "
    SELECT
        c.empresa AS nome_cliente,
        p.nome AS nome_plano,
        ct.identificacao,
        ct.data_inicio,
        ct.data_fim
    FROM contratos ct
    JOIN clientes c ON ct.cliente_id = c.id
    JOIN planos p ON ct.plano_id = p.id
    WHERE ct.data_fim >= CURDATE()
    ORDER BY c.empresa, ct.data_inicio
";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?> (Contratos Ativos)</h1>

<table>
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Plano</th>
            <th>Identificação</th>
            <th>Início do Contrato</th>
            <th>Fim do Contrato</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_plano']); ?></td>
                    <td><?php echo htmlspecialchars($row['identificacao'] ?: 'N/A'); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_inicio'])); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_fim'])); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhum contrato ativo encontrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once  'templates/footer.php';
?>
