<?php
require_once 'init.php';
$page_title = 'Relatório de Débitos de Clientes';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$sql = "
    SELECT
        c.empresa,
        c.email,
        c.telefone,
        SUM(cb.valor) AS total_devido,
        COUNT(cb.id) AS faturas_pendentes
    FROM cobrancas cb
    JOIN clientes c ON c.id = cb.cliente_id
    WHERE cb.pago = 0
    GROUP BY c.id
    ORDER BY total_devido DESC
";

$result = $conn->query($sql);
$debitos = ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar ao Dashboard</a>

<?php if (!empty($debitos)): ?>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Contato (Email/Telefone)</th>
                <th>Faturas Pendentes</th>
                <th>Total Devido</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($debitos as $debito): ?>
                <tr>
                    <td><?php echo htmlspecialchars($debito['empresa']); ?></td>
                    <td><?php echo htmlspecialchars($debito['email'] . ' / ' . $debito['telefone']); ?></td>
                    <td><?php echo $debito['faturas_pendentes']; ?></td>
                    <td style="color: red; font-weight: bold;">R$ <?php echo number_format($debito['total_devido'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhum débito encontrado. Todos os clientes estão em dia.</p>
<?php endif; ?>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
