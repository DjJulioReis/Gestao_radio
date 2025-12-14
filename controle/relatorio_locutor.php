<?php
require_once 'init.php';
$page_title = 'Relatório de Locutor';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Filtros
$locutor_id = filter_input(INPUT_GET, 'locutor_id', FILTER_VALIDATE_INT);
$mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT, ['options' => ['default' => date('m')]]);
$ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT, ['options' => ['default' => date('Y')]]);

// Buscar todos os locutores para o dropdown
$locutores = $conn->query("SELECT id, nome FROM locutores ORDER BY nome");

$dados_relatorio = [];
$total_comissao = 0;

if ($locutor_id) {
    $sql = "
        SELECT
            c.empresa AS cliente,
            p.nome AS plano,
            ct.data_fim,
            p.preco AS valor_plano,
            (p.preco * 0.5) AS comissao
        FROM clientes_locutores cl
        JOIN clientes c ON c.id = cl.cliente_id
        JOIN locutores l ON l.id = cl.locutor_id
        LEFT JOIN contratos ct ON ct.cliente_id = c.id AND MONTH(ct.data_inicio) <= ? AND YEAR(ct.data_inicio) <= ? AND MONTH(ct.data_fim) >= ? AND YEAR(ct.data_fim) >= ?
        LEFT JOIN planos p ON p.id = ct.plano_id
        WHERE l.id = ?
        ORDER BY c.empresa
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $mes, $ano, $mes, $ano, $locutor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dados_relatorio[] = $row;
            $total_comissao += $row['comissao'];
        }
    }
    $stmt->close();
}
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar ao Dashboard</a>

<form method="get" class="filter-form">
    <div class="form-group">
        <label for="locutor_id">Selecione o Locutor:</label>
        <select name="locutor_id" id="locutor_id" required onchange="this.form.submit()">
            <option value="">--Selecione--</option>
            <?php while ($locutor = $locutores->fetch_assoc()): ?>
                <option value="<?php echo $locutor['id']; ?>" <?php echo ($locutor['id'] == $locutor_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($locutor['nome']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <!-- Filtros de data podem ser adicionados aqui se necessário -->
</form>

<?php if ($locutor_id && !empty($dados_relatorio)): ?>
    <h2>Relatório para <?php echo htmlspecialchars(array_column($locutores->fetch_all(MYSQLI_ASSOC), 'nome', 'id')[$locutor_id]); ?></h2>

    <div class="summary">
        <p><strong>Total de Comissão:</strong> <span style="color: blue; font-weight: bold;">R$ <?php echo number_format($total_comissao, 2, ',', '.'); ?></span></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Plano</th>
                <th>Comissão</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados_relatorio as $linha): ?>
                <tr>
                    <td><?php echo htmlspecialchars($linha['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($linha['plano']); ?></td>
                    <td>R$ <?php echo number_format($linha['comissao'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($locutor_id): ?>
    <p>Nenhum dado encontrado para este locutor no período selecionado.</p>
<?php endif; ?>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
