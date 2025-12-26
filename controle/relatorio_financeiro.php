<?php
require_once 'init.php';
$page_title = 'Relatório Financeiro';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Filtros
$mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT, ['options' => ['default' => date('m')]]);
$ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT, ['options' => ['default' => date('Y')]]);

// Entradas (Cobranças Pagas)
$stmt_entradas = $conn->prepare("SELECT SUM(valor) as total FROM cobrancas WHERE pago = 1 AND MONTH(data_pagamento) = ? AND YEAR(data_pagamento) = ?");
$stmt_entradas->bind_param("ii", $mes, $ano);
$stmt_entradas->execute();
$total_entradas_cobrancas = $stmt_entradas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_entradas->close();

// Entradas (Investimentos)
$stmt_investimentos = $conn->prepare("SELECT SUM(valor) as total FROM investimentos_socios WHERE MONTH(data) = ? AND YEAR(data) = ?");
$stmt_investimentos->bind_param("ii", $mes, $ano);
$stmt_investimentos->execute();
$total_investimentos = $stmt_investimentos->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_investimentos->close();

$total_entradas = $total_entradas_cobrancas + $total_investimentos;

// Saídas (Despesas Pagas)
$stmt_saidas = $conn->prepare("SELECT SUM(valor) as total FROM despesas WHERE pago = 1 AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ?");
$stmt_saidas->bind_param("ii", $mes, $ano);
$stmt_saidas->execute();
$total_saidas = $stmt_saidas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_saidas->close();

// Comissões (Saída Adicional)
$stmt_comissoes = $conn->prepare("
    SELECT SUM(p.preco * 0.5) as total
    FROM cobrancas cb
    JOIN contratos ct ON cb.contrato_id = ct.id
    JOIN planos p ON ct.plano_id = p.id
    JOIN cliente_colaboradores cl ON ct.cliente_id = cl.cliente_id
    WHERE cb.pago = 1 AND MONTH(cb.data_pagamento) = ? AND YEAR(cb.data_pagamento) = ?
");
$stmt_comissoes->bind_param("ii", $mes, $ano);
$stmt_comissoes->execute();
$total_comissoes = $stmt_comissoes->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_comissoes->close();

$total_saidas_final = $total_saidas + $total_comissoes;
$lucro = $total_entradas - $total_saidas_final;
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar ao Dashboard</a>

<form method="get" class="filter-form">
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
        <input type="number" name="ano" id="ano" value="<?php echo $ano; ?>" style="width: 80px;">
    </div>
    <button type="submit">Filtrar</button>
</form>

<div class="summary">
    <h2>Resumo para <?php echo strftime('%B', mktime(0, 0, 0, $mes, 1)); ?> de <?php echo $ano; ?></h2>
    <p><strong>Entradas (Cobranças):</strong> <span style="color: green;">R$ <?php echo number_format($total_entradas_cobrancas, 2, ',', '.'); ?></span></p>
    <p><strong>Entradas (Investimentos):</strong> <span style="color: green;">R$ <?php echo number_format($total_investimentos, 2, ',', '.'); ?></span></p>
    <p><strong>Total de Entradas:</strong> <span style="color: darkgreen; font-weight: bold;">R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></span></p>
    <hr>
    <p><strong>Saídas (Despesas):</strong> <span style="color: red;">R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></span></p>
    <p><strong>Saídas (Comissões):</strong> <span style="color: red;">R$ <?php echo number_format($total_comissoes, 2, ',', '.'); ?></span></p>
    <p><strong>Total de Saídas:</strong> <span style="color: darkred; font-weight: bold;">R$ <?php echo number_format($total_saidas_final, 2, ',', '.'); ?></span></p>
    <hr>
    <p><strong>Lucro/Prejuízo:</strong> <span style="color: <?php echo ($lucro >= 0) ? 'blue;' : 'darkred;'; ?>; font-weight: bold;">R$ <?php echo number_format($lucro, 2, ',', '.'); ?></span></p>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
