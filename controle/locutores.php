<?php
require_once 'init.php';
$page_title = "Gestão de Locutores";
require_once 'templates/header.php';

$sql = "SELECT id, nome, email, telefone FROM locutores ORDER BY nome";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<?php if ($_SESSION['user_level'] === 'admin'): ?>
    <a href="locutor_add.php" class="add-link">Adicionar Novo Locutor</a>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
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
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo isset($row['telefone']) ? htmlspecialchars($row['telefone']) : 'Não informado'; ?></td>
                    <?php if ($_SESSION['user_level'] === 'admin'): ?>
                        <td class="actions">
                            <a href="locutor_edit.php?id=<?php echo $row['id']; ?>">Editar</a>
                            <a href="src/locutor_delete_handler.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza?');">Excluir</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo ($_SESSION['user_level'] === 'admin') ? '3' : '2'; ?>">Nenhum locutor cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<?php
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
