$page_title = "Gestão de Tipos de Anúncio";
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/src/db_connect.php';

$sql = "SELECT id, nome FROM tipos_anuncio ORDER BY nome";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<?php if ($_SESSION['user_level'] === 'admin'): ?>
    <a href="tipo_anuncio_add.php" class="add-link">Adicionar Novo Tipo</a>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nome</th>
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
                    <?php if ($_SESSION['user_level'] === 'admin'): ?>
                        <td class="actions">
                            <a href="tipo_anuncio_edit.php?id=<?php echo $row['id']; ?>">Editar</a>
                            <a href="src/tipo_anuncio_delete_handler.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza?');">Excluir</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo ($_SESSION['user_level'] === 'admin') ? '2' : '1'; ?>">Nenhum tipo de anúncio cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
