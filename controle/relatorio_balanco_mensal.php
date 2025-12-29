<?php
require_once 'init.php';
$page_title = 'Balanço Mensal Detalhado';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Filtros
$mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m');
$ano = date('Y', strtotime($mes_ano));
$mes = date('m', strtotime($mes_ano));

// --- CÁLCULOS BASEADOS EM COMPETÊNCIA ---

// 1. Entradas: Cobranças com referência no mês selecionado (pagas ou não)
$sql_entradas = "
    SELECT c.empresa, cb.valor, cb.pago, cb.data_pagamento
    FROM cobrancas cb
    JOIN clientes c ON cb.cliente_id = c.id
    WHERE cb.referencia = ?
    ORDER BY c.empresa
";
$stmt_entradas = $conn->prepare($sql_entradas);
$stmt_entradas->bind_param("s", $mes_ano);
$stmt_entradas->execute();
$entradas = $stmt_entradas->get_result()->fetch_all(MYSQLI_ASSOC);
$total_entradas = array_sum(array_column($entradas, 'valor'));
$stmt_entradas->close();

// 2. Saídas: Despesas com vencimento no mês selecionado
$sql_saidas = "
    SELECT descricao, valor, pago, data_pagamento
    FROM despesas
    WHERE MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ?
    ORDER BY descricao
";
$stmt_saidas = $conn->prepare($sql_saidas);
$stmt_saidas->bind_param("is", $mes, $ano);
$stmt_saidas->execute();
$saidas_despesas = $stmt_saidas->get_result()->fetch_all(MYSQLI_ASSOC);
$total_saidas_despesas = array_sum(array_column($saidas_despesas, 'valor'));
$stmt_saidas->close();

// 3. Saídas: Comissões de cobranças do mês (apenas de não-sócios)
$sql_comissoes = "
    SELECT
        cl.empresa,
        col.nome as colaborador,
        (cb.valor * cc.percentual_comissao / 100) AS valor_comissao,
        cb.pago
    FROM cobrancas cb
    JOIN cliente_colaboradores cc ON cb.cliente_id = cc.cliente_id
    JOIN colaboradores col ON cc.colaborador_id = col.id
    JOIN clientes cl ON cb.cliente_id = cl.id
    LEFT JOIN socios s ON col.id = s.colaborador_id
    WHERE
        cb.referencia = ?
        AND (col.funcao != 'socio_locutor' OR s.reinvestir_comissao = 0)
    ORDER BY cl.empresa
";
$stmt_comissoes = $conn->prepare($sql_comissoes);
$stmt_comissoes->bind_param("s", $mes_ano);
$stmt_comissoes->execute();
$saidas_comissoes = $stmt_comissoes->get_result()->fetch_all(MYSQLI_ASSOC);
$total_saidas_comissoes = array_sum(array_column($saidas_comissoes, 'valor_comissao'));
$stmt_comissoes->close();


$total_saidas = $total_saidas_despesas + $total_saidas_comissoes;
$balanco_final = $total_entradas - $total_saidas;

?>

<h1><?php echo $page_title; ?> (Competência: <?php echo date('m/Y', strtotime($mes_ano)); ?>)</h1>
<a href="dashboard.php">voltar ao inicio</a>

<form method="get" action="" class="filter-form">
    <div class="form-group">
        <label for="mes_ano">Selecionar Mês:</label>
        <input type="month" name="mes_ano" id="mes_ano" value="<?php echo $mes_ano; ?>">
        <button type="submit">Gerar Relatório</button>
    </div>
</form>

<div class="summary-container">
    <div class="summary-card">
        <h3>Total de Entradas (Previsto)</h3>
        <p class="income">R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></p>
    </div>
    <div class="summary-card">
        <h3>Total de Saídas (Previsto)</h3>
        <p class="expense">R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></p>
    </div>
    <div class="summary-card">
        <h3>Balanço Final (Previsto)</h3>
        <p class="<?php echo $balanco_final >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
            R$ <?php echo number_format($balanco_final, 2, ',', '.'); ?>
        </p>
    </div>
</div>

<div class="details-container">
    <div class="column">
        <h2>Entradas (Cobranças)</h2>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entradas as $entrada): ?>
                <tr>
                    <td><?php echo htmlspecialchars($entrada['empresa']); ?></td>
                    <td>R$ <?php echo number_format($entrada['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo $entrada['pago'] ? '<span style="color:green;">Pago</span>' : '<span style="color:red;">Pendente</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="column">
        <h2>Saídas (Despesas e Comissões)</h2>
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($saidas_despesas as $despesa): ?>
                <tr>
                    <td><?php echo htmlspecialchars($despesa['descricao']); ?></td>
                    <td>R$ <?php echo number_format($despesa['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo $despesa['pago'] ? '<span style="color:green;">Pago</span>' : '<span style="color:red;">Pendente</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($saidas_comissoes as $comissao): ?>
                <tr>
                    <td>Comissão: <?php echo htmlspecialchars($comissao['colaborador']) . ' (' . htmlspecialchars($comissao['empresa']) . ')'; ?></td>
                    <td>R$ <?php echo number_format($comissao['valor_comissao'], 2, ',', '.'); ?></td>
                    <td><?php echo $comissao['pago'] ? '<span style="color:green;">Pago</span>' : '<span style="color:red;">Pendente</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<style>
.details-container {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}
.column {
    width: 48%;
}
.summary-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}
.summary-card {
    text-align: center;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 30%;
}
.income { color: green; font-weight: bold; }
.expense { color: red; font-weight: bold; }
.balance-positive { color: blue; font-weight: bold; }
.balance-negative { color: darkred; font-weight: bold; }
</style>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
