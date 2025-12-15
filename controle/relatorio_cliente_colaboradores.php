<?php
require_once 'init.php';
$page_title = "Relatório de Locutores e Clientes";
require_once 'templates/header.php';

$sql = "
    SELECT
        l.nome AS locutor,
        c.empresa AS cliente,
        c.telefone AS cliente_telefone,
        c.email AS cliente_email
    FROM
        locutores l
    JOIN
        clientes_locutores cl ON l.id = cl.locutor_id
    JOIN
        clientes c ON cl.cliente_id = c.id
    ORDER BY
        l.nome, c.empresa
";

$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<table>
    <thead>
        <tr>
            <th>Locutor</th>
            <th>Cliente</th>
            <th>Telefone do Cliente</th>
            <th>Email do Cliente</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['locutor']); ?></td>
                    <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($row['cliente_telefone']); ?></td>
                    <td><?php echo htmlspecialchars($row['cliente_email']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Nenhuma relação locutor-cliente encontrada.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
