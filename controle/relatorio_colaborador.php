<?php
require_once 'init.php';
$page_title = 'Relatório de Comissão de Colaborador';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Filtros
$colaborador_id = filter_input(INPUT_GET, 'colaborador_id', FILTER_VALIDATE_INT);
$mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT, ['options' => ['default' => date('m')]]);
$ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT, ['options' => ['default' => date('Y')]]);

// Buscar todos os colaboradores (apenas locutores) para o dropdown
$colaboradores = $conn->query("SELECT id, nome FROM colaboradores WHERE funcao IN ('locutor', 'socio_locutor') ORDER BY nome");

$dados_relatorio = [];
$total_comissao = 0;
$colaborador_nome = '';

if ($colaborador_id) {
    // Buscar o nome do colaborador selecionado
    $stmt_nome = $conn->prepare("SELECT nome FROM colaboradores WHERE id = ?");
    $stmt_nome->bind_param("i", $colaborador_id);
    $stmt_nome->execute();
    $result_nome = $stmt_nome->get_result();
    if ($result_nome->num_rows > 0) {
        $colaborador_nome = $result_nome->fetch_assoc()['nome'];
    }
    $stmt_nome->close();

    $primeiro_dia_mes = "{$ano}-{$mes}-01";
    $ultimo_dia_mes = date("Y-m-t", strtotime($primeiro_dia_mes));

    // Busca todos os contratos associados ao colaborador que estão ativos no mês/ano selecionado
    $sql = "
        SELECT
            c.empresa AS cliente,
            ct.identificacao AS identificacao_contrato,
            ct.valor,
            (ct.valor * cc.percentual_comissao / 100) AS comissao
        FROM cliente_colaboradores cc
        JOIN clientes c ON c.id = cc.cliente_id
        JOIN colaboradores col ON col.id = cc.colaborador_id
        JOIN contratos ct ON ct.cliente_id = c.id
        WHERE cc.colaborador_id = ?
          AND ct.data_inicio <= ?
          AND ct.data_fim >= ?
        ORDER BY c.empresa;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $colaborador_id, $ultimo_dia_mes, $primeiro_dia_mes);
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
        <label for="colaborador_id">Selecione o Colaborador:</label>
        <select name="colaborador_id" id="colaborador_id" required onchange="this.form.submit()">
            <option value="">--Selecione--</option>
            <?php
            // Reset pointer to loop again for the dropdown
            $colaboradores->data_seek(0);
            while ($colaborador = $colaboradores->fetch_assoc()):
            ?>
                <option value="<?php echo $colaborador['id']; ?>" <?php echo ($colaborador['id'] == $colaborador_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($colaborador['nome']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
     <div class="form-group">
        <label for="mes">Mês:</label>
        <select name="mes" id="mes">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($m == $mes) ? 'selected' : ''; ?>>
                    <?php echo strftime('%B', mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ano">Ano:</label>
        <input type="number" name="ano" id="ano" value="<?php echo $ano; ?>">
    </div>
    <button type="submit">Filtrar</button>
</form>

<?php if ($colaborador_id && !empty($dados_relatorio)): ?>
    <h2>Relatório para <?php echo htmlspecialchars($colaborador_nome); ?></h2>

    <div class="summary">
        <p><strong>Total de Comissão:</strong> <span style="color: blue; font-weight: bold;">R$ <?php echo number_format($total_comissao, 2, ',', '.'); ?></span></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Contrato</th>
                <th>Valor do Contrato</th>
                <th>Comissão</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados_relatorio as $linha): ?>
                <tr>
                    <td><?php echo htmlspecialchars($linha['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($linha['identificacao_contrato']); ?></td>
                    <td>R$ <?php echo number_format($linha['valor'], 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($linha['comissao'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($colaborador_id): ?>
    <p>Nenhum dado encontrado para este colaborador no período selecionado.</p>
<?php endif; ?>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
