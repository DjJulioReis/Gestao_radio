<?php
require_once 'init.php';
$page_title = "Relatório Mensal";

require_once 'templates/header.php';


// Define o mês e ano para o filtro, padrão para o mês atual
$mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m');
$ano = date('Y', strtotime($mes_ano));
$mes = date('m', strtotime($mes_ano));

// 1. Calcular Entradas (Receita de Contratos Ativos no Mês)
$sql_entradas = "
    SELECT SUM(p.preco) AS total_receita
    FROM contratos c
    JOIN planos p ON c.plano_id = p.id
    WHERE YEAR(c.data_inicio) <= ? AND MONTH(c.data_inicio) <= ?
      AND YEAR(c.data_fim) >= ? AND MONTH(c.data_fim) >= ?
";
$stmt_entradas = $conn->prepare($sql_entradas);
$stmt_entradas->bind_param("iiii", $ano, $mes, $ano, $mes);
$stmt_entradas->execute();
$result_entradas = $stmt_entradas->get_result();
$total_receita = $result_entradas->fetch_assoc()['total_receita'] ?? 0;

// 2. Calcular Saídas (Despesas do Mês)
$sql_saidas = "SELECT SUM(valor) AS total_despesas FROM despesas WHERE YEAR(data_vencimento) = ? AND MONTH(data_vencimento) = ?";
$stmt_saidas = $conn->prepare($sql_saidas);
$stmt_saidas->bind_param("ii", $ano, $mes);
$stmt_saidas->execute();
$result_saidas = $stmt_saidas->get_result();
$total_despesas = $result_saidas->fetch_assoc()['total_despesas'] ?? 0;

// 3. Calcular o Balanço
$balanco = $total_receita - $total_despesas;
?>

<style>
    .report-summary { border: 1px solid #ccc; padding: 20px; margin-top: 20px; background-color: #fff; border-radius: 8px; }
    .report-summary h2 { margin-top: 0; }
    .entradas { color: #2980b9; }
    .saidas { color: #e67e22; }
    .balanco-positivo { color: #27ae60; }
    .balanco-negativo { color: #c0392b; }
</style>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar para o Dashboard</a>
<form method="get" action="">
    <div class="form-group">
        <label for="mes_ano">Selecionar Mês:</label>
        <input type="month" name="mes_ano" id="mes_ano" value="<?php echo $mes_ano; ?>">
        <button type="submit">Gerar Relatório</button>
    </div>
</form>

<div class="report-summary">
    <h2>Resumo para <?php echo date('m/Y', strtotime($mes_ano)); ?></h2>
    <p class="entradas"><strong>Total de Entradas (Receita):</strong> R$ <?php echo number_format($total_receita, 2, ',', '.'); ?></p>
    <p class="saidas"><strong>Total de Saídas (Despesas):</strong> R$ <?php echo number_format($total_despesas, 2, ',', '.'); ?></p>
    <hr>
    <p class="balanco-<?php echo ($balanco >= 0) ? 'positivo' : 'negativo'; ?>">
        <strong>Balanço do Mês: R$ <?php echo number_format($balanco, 2, ',', '.'); ?></strong>
    </p>
</div>

<?php
$stmt_entradas->close();
$stmt_saidas->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
