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

// Entradas (Investimentos) - Apenas para exibição
$stmt_investimentos = $conn->prepare("SELECT SUM(valor) as total FROM investimentos_socios WHERE tipo = 'investimento' AND MONTH(data) = ? AND YEAR(data) = ?");
$stmt_investimentos->bind_param("ii", $mes, $ano);
$stmt_investimentos->execute();
$total_investimentos = $stmt_investimentos->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_investimentos->close();

// Total de Entradas para o cálculo do lucro (apenas operacional)
$total_entradas = $total_entradas_cobrancas;

// Saídas (Despesas Pagas)
$stmt_saidas = $conn->prepare("SELECT SUM(valor) as total FROM despesas WHERE pago = 1 AND MONTH(data_pagamento) = ? AND YEAR(data_pagamento) = ?");
$stmt_saidas->bind_param("ii", $mes, $ano);
$stmt_saidas->execute();
$total_saidas = $stmt_saidas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_saidas->close();

// Comissões (Saída Adicional - todas)
$stmt_comissoes = $conn->prepare("
    SELECT SUM(ct.valor * cc.percentual_comissao / 100) as total
    FROM cobrancas cb
    JOIN contratos ct ON cb.contrato_id = ct.id
    JOIN cliente_colaboradores cc ON ct.cliente_id = cc.cliente_id
    WHERE cb.pago = 1 AND MONTH(cb.data_pagamento) = ? AND YEAR(cb.data_pagamento) = ?
");
$stmt_comissoes->bind_param("ii", $mes, $ano);
$stmt_comissoes->execute();
$total_comissoes = $stmt_comissoes->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_comissoes->close();

$total_saidas_final = $total_saidas + $total_comissoes;
$lucro = $total_entradas - $total_saidas_final;

// Query para buscar a lista detalhada de investimentos
$sql_investimentos_detalhe = "
    SELECT
        c.nome AS socio_nome,
        i.valor,
        i.data,
        i.descricao
    FROM investimentos_socios i
    JOIN colaboradores c ON i.socio_id = c.id
    WHERE MONTH(i.data) = ? AND YEAR(i.data) = ? AND i.tipo = 'investimento'
    ORDER BY i.data
";
$stmt_investimentos_detalhe = $conn->prepare($sql_investimentos_detalhe);
$stmt_investimentos_detalhe->bind_param("is", $mes, $ano);
$stmt_investimentos_detalhe->execute();
$investimentos_detalhados = $stmt_investimentos_detalhe->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_investimentos_detalhe->close();
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">voltar ao inicio</a>

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
    <p><strong>Total de Entradas (Operacional):</strong> <span style="color: darkgreen; font-weight: bold;">R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></span></p>
    <hr>
    <p><strong>Saídas (Despesas):</strong> <span style="color: red;">R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></span></p>
    <p><strong>Saídas (Comissões):</strong> <span style="color: red;">R$ <?php echo number_format($total_comissoes, 2, ',', '.'); ?></span></p>
    <p><strong>Total de Saídas (Operacional):</strong> <span style="color: darkred; font-weight: bold;">R$ <?php echo number_format($total_saidas_final, 2, ',', '.'); ?></span></p>
    <hr>
    <p><strong>Lucro/Prejuízo (Operacional):</strong> <span style="color: <?php echo ($lucro >= 0) ? 'blue;' : 'darkred;'; ?>; font-weight: bold;">R$ <?php echo number_format($lucro, 2, ',', '.'); ?></span></p>
    <hr>
    <p><strong>Total de Investimentos no Mês:</strong> <span style="color: blue; font-weight: bold;">R$ <?php echo number_format($total_investimentos, 2, ',', '.'); ?></span></p>
</div>

<div class="details-section" style="margin-top: 30px;">
    <h2>Detalhes dos Investimentos no Mês</h2>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Sócio</th>
                <th>Descrição</th>
                <th style="text-align: right;">Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($investimentos_detalhados)): ?>
                <?php foreach ($investimentos_detalhados as $investimento): ?>
                    <tr>
                        <td><?php echo date("d/m/Y", strtotime($investimento['data'])); ?></td>
                        <td><?php echo htmlspecialchars($investimento['socio_nome']); ?></td>
                        <td><?php echo htmlspecialchars($investimento['descricao']); ?></td>
                        <td style="text-align: right;">R$ <?php echo number_format($investimento['valor'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nenhum investimento encontrado para este mês.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
