<?php
$page_title = "Gestão de Contratos";
require_once __DIR__ . '/templates/header.php';
// A conexão com o banco de dados já é feita no init.php, incluído pelo header.php

$sql = "
    SELECT
        ct.id,
        ct.identificacao,
        c.empresa AS cliente_nome,
        p.nome AS plano_nome,
        ct.data_inicio,
        ct.data_fim
    FROM contratos ct
    JOIN clientes c ON ct.cliente_id = c.id
    JOIN planos p ON ct.plano_id = p.id
    ORDER BY ct.data_inicio DESC
";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<?php if ($_SESSION['user_level'] === 'admin'): ?>
    <a href="contrato_add.php" class="add-link">Adicionar Novo Contrato</a>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Identificação</th>
            <th>Cliente</th>
            <th>Plano</th>
            <th>Data de Início</th>
            <th>Data de Fim</th>
            <?php if ($_SESSION['user_level'] === 'admin'): ?>
                <th>Ações</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['identificacao'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['cliente_nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['plano_nome']); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_inicio'])); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_fim'])); ?></td>
                    <?php if ($_SESSION['user_level'] === 'admin'): ?>
                        <td class="actions">
                            <a href="contrato_edit.php?id=<?php echo $row['id']; ?>">Editar</a>
                            <a href="src/contrato_delete_handler.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza?');">Excluir</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo ($_SESSION['user_level'] === 'admin') ? '6' : '5'; ?>">Nenhum contrato cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
