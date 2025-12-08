<?php
require_once 'init.php';
$page_title = "Gestão de Clientes";
require_once 'templates/header.php';

// Agora buscamos também data_cadastro e ativo
$sql = "SELECT c.id, c.empresa, c.cnpj_cpf, c.email, c.telefone, 
               c.data_cadastro, c.plano_id, c.ativo,
               p.nome AS plano
        FROM clientes c
        LEFT JOIN planos p ON c.plano_id = p.id
        ORDER BY c.empresa";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Inicio</a>

<?php if ($_SESSION['user_level'] === 'admin'): ?>
    <a href="cliente_add.php" class="add-link">Adicionar Novo Cliente</a>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Empresa</th>
            <th>CNPJ/CPF</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Data Cadastro</th>
            <th>Plano</th>
            <th>Ativo</th>
            <?php if ($_SESSION['user_level'] === 'admin'): ?>
                <th>Ações</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

                <?php
                    // Badge de ativo/inativo
                    $badge = $row['ativo'] 
                        ? '<span style="color: green; font-weight: bold;">Ativo</span>'
                        : '<span style="color: red; font-weight: bold;">Inativo</span>';

                    // Formatar data (YYYY-MM-DD → DD/MM/YYYY)
                    $dataFormatada = $row['data_cadastro']
                        ? date("d/m/Y", strtotime($row['data_cadastro']))
                        : "-";
                ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                    <td><?php echo htmlspecialchars($row['cnpj_cpf']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                    <td><?php echo $dataFormatada; ?></td>
                    <td><?php echo $row['plano'] ? htmlspecialchars($row['plano']) : '—'; ?></td>
                    <td><?php echo $badge; ?></td>

                    <?php if ($_SESSION['user_level'] === 'admin'): ?>
                        <td class="actions">
                            <a href="cliente_edit.php?id=<?php echo $row['id']; ?>">Editar</a>
                            <a href="src/cliente_delete_handler.php?id=<?php echo $row['id']; ?>"
                               onclick="return confirm('Tem certeza que deseja excluir este cliente?');">
                                Excluir
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>

            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo ($_SESSION['user_level'] === 'admin') ? '7' : '6'; ?>">
                    Nenhum cliente cadastrado.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once  'templates/footer.php';
?>
