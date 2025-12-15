<?php
require_once 'init.php';
$page_title = "Gestão de Sócios";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Lógica para processar o formulário de reinvestimento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['socio_id'])) {
    $socio_id = filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT);
    $reinvestir = isset($_POST['reinvestir']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE socios SET reinvestir_comissao = ? WHERE colaborador_id = ?");
    $stmt->bind_param("ii", $reinvestir, $socio_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Status de reinvestimento atualizado.";
    } else {
        $_SESSION['error_message'] = "Erro ao atualizar o status.";
    }
    $stmt->close();
    // Redireciona para evitar reenvio do formulário
    header("Location: socios.php");
    exit();
}


// SQL para buscar todos os colaboradores que são sócios e seus dados financeiros
$sql = "
    SELECT
        c.id,
        c.nome,
        c.email,
        s.reinvestir_comissao,
        s.saldo_investido
    FROM colaboradores c
    JOIN socios s ON c.id = s.colaborador_id
    WHERE c.funcao IN ('socio', 'socio_locutor')
    ORDER BY c.nome
";
$result = $conn->query($sql);
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Saldo Investido (R$)</th>
            <th>Reinvestir Comissão?</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo number_format($row['saldo_investido'], 2, ',', '.'); ?></td>
                    <td>
                        <form action="socios.php" method="post" style="margin: 0;">
                            <input type="hidden" name="socio_id" value="<?php echo $row['id']; ?>">
                            <input type="checkbox" name="reinvestir" <?php echo ($row['reinvestir_comissao'] ? 'checked' : ''); ?> onchange="this.form.submit()">
                            <span class="slider round"></span>
                        </form>
                    </td>
                    <td class="actions">
                        <a href="investimentos_socios.php?id=<?php echo $row['id']; ?>">Ver/Adicionar Investimentos</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhum sócio encontrado. Adicione um colaborador com a função 'Sócio' ou 'Sócio Locutor'.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>