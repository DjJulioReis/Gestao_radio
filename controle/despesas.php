<?php
require_once 'init.php';
$page_title = "Controle de Despesas";
require_once 'templates/header.php';

// Filtro (não implementado ainda, mas a estrutura está aqui)
$filtro_mes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');

$sql = "SELECT id, descricao, valor, data_vencimento, tipo, pago, observacao, recibo_path FROM despesas ORDER BY data_vencimento DESC";
$result = $conn->query($sql);
?>

<style>
    .pago-sim { color: green; font-weight: bold; }
    .pago-nao { color: red; font-weight: bold; }
</style>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<?php if ($_SESSION['user_level'] === 'admin'): ?>
    <a href="despesa_add.php" class="add-link">Adicionar Nova Despesa</a>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Descrição</th>
            <th>Valor (R$)</th>
            <th>Vencimento</th>
            <th>Tipo</th>
            <th>Status</th>
            <th>Observação</th>
            <th>Recibo</th>
            <?php if ($_SESSION['user_level'] === 'admin'): ?>
                <th>Ações</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                    <td><?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo date("d/m/Y", strtotime($row['data_vencimento'])); ?></td>
                    <td><?php echo ucfirst($row['tipo']); ?></td>
                    <td class="<?php echo $row['pago'] ? 'pago-sim' : 'pago-nao'; ?>">
                        <?php echo $row['pago'] ? 'Pago' : 'Pendente'; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['observacao'] ?? ''); ?></td>
                    <td>
                        <?php if (!empty($row['recibo_path'])): ?>
                            <a href="<?php echo htmlspecialchars($row['recibo_path']); ?>" target="_blank">Ver</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <?php if ($_SESSION['user_level'] === 'admin'): ?>
                        <td class="actions">
                            <a href="despesa_edit.php?id=<?php echo $row['id']; ?>" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                            <a href="src/despesa_delete_handler.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza?');" title="Excluir"><i class="fas fa-trash-alt"></i></a>
                            <?php if (!$row['pago']): ?>
                                <a href="src/despesa_pago_handler.php?id=<?php echo $row['id']; ?>" title="Marcar como Pago"><i class="fas fa-check-circle"></i></a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo ($_SESSION['user_level'] === 'admin') ? '8' : '7'; ?>">Nenhuma despesa cadastrada.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
