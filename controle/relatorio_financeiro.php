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

// --- CÁLCULO DO RESULTADO DO MÊS (FLUXO DE CAIXA) ---

// 1. Entradas de Cobranças (dinheiro dos clientes)
$stmt_cobrancas = $conn->prepare("SELECT SUM(valor) as total FROM cobrancas WHERE pago = 1 AND MONTH(data_pagamento) = ? AND YEAR(data_pagamento) = ?");
$stmt_cobrancas->bind_param("ii", $mes, $ano);
$stmt_cobrancas->execute();
$total_entradas_cobrancas = $stmt_cobrancas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_cobrancas->close();

// 2. Entradas de Comissões Reinvestidas (dinheiro que ficou no caixa)
$stmt_reinvestidas = $conn->prepare("SELECT SUM(valor) as total FROM investimentos_socios WHERE tipo = 'investimento' AND MONTH(data) = ? AND YEAR(data) = ? AND descricao LIKE 'Comissão reinvestida%'");
$stmt_reinvestidas->bind_param("ii", $mes, $ano);
$stmt_reinvestidas->execute();
$total_comissoes_reinvestidas = $stmt_reinvestidas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_reinvestidas->close();

$total_entradas = $total_entradas_cobrancas + $total_comissoes_reinvestidas;

// 3. Saídas de Despesas (contas pagas)
$stmt_despesas = $conn->prepare("SELECT SUM(valor) as total FROM despesas WHERE pago = 1 AND MONTH(data_pagamento) = ? AND YEAR(data_pagamento) = ?");
$stmt_despesas->bind_param("ii", $mes, $ano);
$stmt_despesas->execute();
$total_saidas_despesas = $stmt_despesas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_despesas->close();

// 4. Saídas de Comissões Pagas (dinheiro que saiu para colaboradores)
$stmt_comissoes_pagas = $conn->prepare("SELECT SUM(cb.valor * cc.percentual_comissao / 100) as total FROM cobrancas cb JOIN cliente_colaboradores cc ON cb.cliente_id = cc.cliente_id JOIN colaboradores col ON cc.colaborador_id = col.id LEFT JOIN socios s ON col.id = s.colaborador_id WHERE cb.pago = 1 AND MONTH(cb.data_pagamento) = ? AND YEAR(cb.data_pagamento) = ? AND (col.funcao != 'socio_locutor' OR s.reinvestir_comissao = 0)");
$stmt_comissoes_pagas->bind_param("ii", $mes, $ano);
$stmt_comissoes_pagas->execute();
$total_comissoes_pagas = $stmt_comissoes_pagas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_comissoes_pagas->close();

$total_saidas = $total_saidas_despesas + $total_comissoes_pagas;
$resultado_mes = $total_entradas - $total_saidas;

// --- CÁLCULO DE APORTES DE CAPITAL (INVESTIMENTOS DIRETOS) ---
$stmt_aportes = $conn->prepare("SELECT SUM(valor) as total FROM investimentos_socios WHERE tipo = 'investimento' AND MONTH(data) = ? AND YEAR(data) = ? AND (descricao NOT LIKE 'Comissão reinvestida%' OR descricao IS NULL)");
$stmt_aportes->bind_param("ii", $mes, $ano);
$stmt_aportes->execute();
$total_aportes_capital = $stmt_aportes->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_aportes->close();

// --- QUERIES PARA DETALHES ---
$despesas_detalhadas = $conn->query("SELECT * FROM despesas WHERE pago = 1 AND MONTH(data_pagamento) = $mes AND YEAR(data_pagamento) = $ano ORDER BY data_pagamento")->fetch_all(MYSQLI_ASSOC);
$comissoes_pagas_detalhadas = $conn->query("SELECT col.nome as colaborador_nome, cl.empresa as cliente_nome, (ct.valor * cc.percentual_comissao / 100) as valor_comissao, cb.data_pagamento FROM cobrancas cb JOIN contratos ct ON cb.contrato_id = ct.id JOIN cliente_colaboradores cc ON ct.cliente_id = cc.cliente_id JOIN colaboradores col ON cc.colaborador_id = col.id JOIN clientes cl ON cb.cliente_id = cl.id LEFT JOIN socios s ON col.id = s.colaborador_id WHERE cb.pago = 1 AND MONTH(cb.data_pagamento) = $mes AND YEAR(cb.data_pagamento) = $ano AND (col.funcao != 'socio_locutor' OR s.reinvestir_comissao = 0) ORDER BY cb.data_pagamento")->fetch_all(MYSQLI_ASSOC);
$comissoes_reinvestidas_detalhadas = $conn->query("SELECT c.nome AS socio_nome, i.valor, i.data, i.descricao FROM investimentos_socios i JOIN colaboradores c ON i.socio_id = c.id WHERE MONTH(i.data) = $mes AND YEAR(i.data) = $ano AND i.tipo = 'investimento' AND i.descricao LIKE 'Comissão reinvestida%' ORDER BY i.data")->fetch_all(MYSQLI_ASSOC);
$aportes_capital_detalhados = $conn->query("SELECT c.nome AS socio_nome, i.valor, i.data, i.descricao FROM investimentos_socios i JOIN colaboradores c ON i.socio_id = c.id WHERE MONTH(i.data) = $mes AND YEAR(i.data) = $ano AND i.tipo = 'investimento' AND (i.descricao NOT LIKE 'Comissão reinvestida%' OR i.descricao IS NULL) ORDER BY i.data")->fetch_all(MYSQLI_ASSOC);

?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">voltar ao inicio</a>

<form method="get" class="filter-form">
    <div class="form-group"><label for="mes">Mês:</label><select name="mes" id="mes"><?php for ($m = 1; $m <= 12; $m++): ?><option value="<?php echo $m; ?>" <?php echo ($m == $mes) ? 'selected' : ''; ?>><?php echo strftime('%B', mktime(0, 0, 0, $m, 1)); ?></option><?php endfor; ?></select></div>
    <div class="form-group"><label for="ano">Ano:</label><input type="number" name="ano" id="ano" value="<?php echo $ano; ?>" style="width: 80px;"></div>
    <button type="submit">Filtrar</button>
</form>

<div class="summary">
    <h2>Resumo para <?php echo strftime('%B', mktime(0, 0, 0, $mes, 1)); ?> de <?php echo $ano; ?></h2>
    <p><strong>(+) Entradas de Cobranças:</strong> <span style="color: green;">R$ <?php echo number_format($total_entradas_cobrancas, 2, ',', '.'); ?></span></p>
    <p><strong>(+) Comissões Reinvestidas:</strong> <span style="color: green;">R$ <?php echo number_format($total_comissoes_reinvestidas, 2, ',', '.'); ?></span></p>
    <p><strong>Total de Entradas:</strong> <span style="color: darkgreen; font-weight: bold;">R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></span></p><hr>
    <p><strong>(-) Saídas (Despesas):</strong> <span style="color: red;">R$ <?php echo number_format($total_saidas_despesas, 2, ',', '.'); ?></span></p>
    <p><strong>(-) Saídas (Comissões Pagas):</strong> <span style="color: red;">R$ <?php echo number_format($total_comissoes_pagas, 2, ',', '.'); ?></span></p>
    <p><strong>Total de Saídas:</strong> <span style="color: darkred; font-weight: bold;">R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></span></p><hr>
    <p><strong>(=) Resultado do Mês (Lucro/Prejuízo):</strong> <span style="color: <?php echo ($resultado_mes >= 0) ? 'blue;' : 'darkred;'; ?>; font-weight: bold;">R$ <?php echo number_format($resultado_mes, 2, ',', '.'); ?></span></p><hr>
    <p><strong>Aportes de Capital (Invest. Diretos):</strong> <span style="color: #8A2BE2; font-weight: bold;">R$ <?php echo number_format($total_aportes_capital, 2, ',', '.'); ?></span></p>
</div>

<div class="details-container">
    <div class="column">
        <h2>Detalhes das Saídas (Despesas)</h2>
        <table>
            <thead><tr><th>Data</th><th>Descrição</th><th style="text-align: right;">Valor</th></tr></thead>
            <tbody>
                <?php if (!empty($despesas_detalhadas)): foreach ($despesas_detalhadas as $item): ?>
                <tr><td><?php echo date("d/m/Y", strtotime($item['data_pagamento'])); ?></td><td><?php echo htmlspecialchars($item['descricao']); ?></td><td style="text-align: right;">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td></tr>
                <?php endforeach; else: ?><tr><td colspan="3">Nenhuma despesa paga neste mês.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="column">
        <h2>Detalhes das Saídas (Comissões Pagas)</h2>
        <table>
            <thead><tr><th>Data</th><th>Colaborador</th><th>Referente a</th><th style="text-align: right;">Valor</th></tr></thead>
            <tbody>
                <?php if (!empty($comissoes_pagas_detalhadas)): foreach ($comissoes_pagas_detalhadas as $item): ?>
                <tr><td><?php echo date("d/m/Y", strtotime($item['data_pagamento'])); ?></td><td><?php echo htmlspecialchars($item['colaborador_nome']); ?></td><td><?php echo htmlspecialchars($item['cliente_nome']); ?></td><td style="text-align: right;">R$ <?php echo number_format($item['valor_comissao'], 2, ',', '.'); ?></td></tr>
                <?php endforeach; else: ?><tr><td colspan="4">Nenhuma comissão foi paga em dinheiro neste mês.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="column">
        <h2>Detalhes das Comissões Reinvestidas</h2>
        <table>
            <thead><tr><th>Data</th><th>Sócio</th><th>Origem</th><th style="text-align: right;">Valor</th></tr></thead>
            <tbody>
                <?php if (!empty($comissoes_reinvestidas_detalhadas)): foreach ($comissoes_reinvestidas_detalhadas as $item): ?>
                <tr>
                    <td><?php echo date("d/m/Y", strtotime($item['data'])); ?></td>
                    <td><?php echo htmlspecialchars($item['socio_nome']); ?></td>
                    <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                    <td style="text-align: right;">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="4">Nenhuma comissão foi reinvestida neste mês.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="column">
        <h2>Detalhes dos Aportes de Capital</h2>
        <table>
            <thead><tr><th>Data</th><th>Sócio</th><th>Descrição</th><th style="text-align: right;">Valor</th></tr></thead>
            <tbody>
                <?php if (!empty($aportes_capital_detalhados)): foreach ($aportes_capital_detalhados as $item): ?>
                <tr><td><?php echo date("d/m/Y", strtotime($item['data'])); ?></td><td><?php echo htmlspecialchars($item['socio_nome']); ?></td><td><?php echo htmlspecialchars($item['descricao']); ?></td><td style="text-align: right;">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td></tr>
                <?php endforeach; else: ?><tr><td colspan="4">Nenhum aporte de capital (investimento direto) neste mês.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.details-container { display: flex; justify-content: space-between; margin-top: 20px; flex-wrap: wrap; }
.column { width: 48%; margin-bottom: 20px; }
</style>

<?php $conn->close(); require_once 'templates/footer.php'; ?>
