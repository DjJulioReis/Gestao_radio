<?php
require_once 'init.php';
$page_title = "Gestão de Planos e Pacotes";
require_once 'templates/header.php';

$sql = "SELECT id, nome, preco, insercoes_mes FROM planos ORDER BY nome";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<?php if ($_SESSION['user_level'] === 'admin'): ?>
    <a href="plano_add.php" class="add-link">Adicionar Novo Plano</a>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nome do Plano</th>
            <th>Preço (R$)</th>
            <th>Inserções/Mês</th>
            <?php if ($_SESSION['user_level'] === 'admin'): ?>
                <th>Ações</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td><?php echo number_format($row['preco'], 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($row['insercoes_mes']); ?></td>
                    <?php if ($_SESSION['user_level'] === 'admin'): ?>
                        <td class="actions">
                            <a href="plano_edit.php?id=<?php echo $row['id']; ?>">Editar</a>
                            <a href="src/plano_delete_handler.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este plano?');">Excluir</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo ($_SESSION['user_level'] === 'admin') ? '4' : '3'; ?>">Nenhum plano cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
