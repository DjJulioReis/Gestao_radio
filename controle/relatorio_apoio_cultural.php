<?php
require_once 'init.php';
$page_title = 'Relatório de Projetos de Apoio Cultural';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$sql = "
    SELECT
        ac.id,
        ac.nome_projeto,
        ac.meta_arrecadacao,
        (SELECT SUM(valor_doado) FROM apoios_clientes WHERE apoio_id = ac.id) as total_arrecadado
    FROM apoios_culturais ac
    ORDER BY ac.nome_projeto
";

$result_projetos = $conn->query($sql);
$projetos = ($result_projetos->num_rows > 0) ? $result_projetos->fetch_all(MYSQLI_ASSOC) : [];
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar ao Dashboard</a>

<?php if (!empty($projetos)): ?>
    <?php foreach ($projetos as $projeto): ?>
        <h2><?php echo htmlspecialchars($projeto['nome_projeto']); ?></h2>
        <div class="summary">
            <p>
                <strong>Meta:</strong> R$ <?php echo number_format($projeto['meta_arrecadacao'], 2, ',', '.'); ?> |
                <strong>Arrecadado:</strong> <span style="color: green; font-weight: bold;">R$ <?php echo number_format($projeto['total_arrecadado'] ?? 0, 2, ',', '.'); ?></span>
            </p>
        </div>

        <?php
        $stmt_apoiadores = $conn->prepare("
            SELECT c.empresa, acl.valor_doado
            FROM apoios_clientes acl
            JOIN clientes c ON c.id = acl.cliente_id
            WHERE acl.apoio_id = ?
            ORDER BY c.empresa
        ");
        $stmt_apoiadores->bind_param("i", $projeto['id']);
        $stmt_apoiadores->execute();
        $result_apoiadores = $stmt_apoiadores->get_result();
        $apoiadores = ($result_apoiadores->num_rows > 0) ? $result_apoiadores->fetch_all(MYSQLI_ASSOC) : [];
        $stmt_apoiadores->close();
        ?>

        <?php if (!empty($apoiadores)): ?>
            <h4>Apoiadores:</h4>
            <ul>
                <?php foreach ($apoiadores as $apoiador): ?>
                    <li><?php echo htmlspecialchars($apoiador['empresa']); ?> - R$ <?php echo number_format($apoiador['valor_doado'], 2, ',', '.'); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nenhum apoiador para este projeto ainda.</p>
        <?php endif; ?>
        <hr>
    <?php endforeach; ?>

<?php else: ?>
    <p>Nenhum projeto de apoio cultural encontrado.</p>
<?php endif; ?>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
