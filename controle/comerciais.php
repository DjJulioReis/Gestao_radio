<?php
require_once 'init.php';
$page_title = "Gestão de Comerciais";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Buscar todos os comerciais com informações do cliente
$sql = "
    SELECT
        co.id,
        co.identificador_arquivo,
        co.duracao,
        co.ativo,
        co.data_upload,
        cl.empresa as nome_cliente
    FROM comerciais co
    JOIN clientes cl ON co.cliente_id = cl.id
    ORDER BY co.data_upload DESC
";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<a href="comercial_add.php" class="add-link">Adicionar Novo Comercial</a>

<table>
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Identificador do Arquivo</th>
            <th>Duração (s)</th>
            <th>Status</th>
            <th>Data de Upload</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                    <td><?php echo htmlspecialchars($row['identificador_arquivo']); ?></td>
                    <td><?php echo $row['duracao']; ?>s</td>
                    <td class="<?php echo $row['ativo'] ? 'pago-sim' : 'pago-nao'; ?>">
                        <?php echo $row['ativo'] ? 'Ativo' : 'Inativo'; ?>
                    </td>
                    <td><?php echo date("d/m/Y H:i", strtotime($row['data_upload'])); ?></td>
                    <td class="actions">
                        <!-- Futuramente: Editar e Excluir -->
                        <a href="#" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                        <a href="#" onclick="return confirm('Tem certeza?');" title="Excluir"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">Nenhum comercial cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'templates/footer.php';
?>