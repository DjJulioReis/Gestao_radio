<?php
require_once 'init.php';
$page_title = 'Balanço Mensal';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Filtros de data
$mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m');
$ano = date('Y', strtotime($mes_ano));
$mes = date('m', strtotime($mes_ano));
$primeiro_dia = "$mes_ano-01";
$ultimo_dia = date("Y-m-t", strtotime($primeiro_dia));
$dias_no_mes = date('t', strtotime($primeiro_dia));

// 1. Calcular Entradas (Cobranças Pagas)
$sql_entradas = "SELECT SUM(valor) AS total FROM cobrancas WHERE pago = 1 AND data_pagamento BETWEEN ? AND ?";
$stmt_entradas = $conn->prepare($sql_entradas);
$stmt_entradas->bind_param("ss", $primeiro_dia, $ultimo_dia);
$stmt_entradas->execute();
$result_entradas = $stmt_entradas->get_result();
$total_entradas = $result_entradas->fetch_assoc()['total'] ?? 0;
$stmt_entradas->close();

// 2. Calcular Saídas (Despesas Pagas)
$sql_saidas = "SELECT SUM(valor) AS total FROM despesas WHERE pago = 1 AND data_vencimento BETWEEN ? AND ?";
$stmt_saidas = $conn->prepare($sql_saidas);
$stmt_saidas->bind_param("ss", $primeiro_dia, $ultimo_dia);
$stmt_saidas->execute();
$result_saidas = $stmt_saidas->get_result();
$total_saidas = $result_saidas->fetch_assoc()['total'] ?? 0;
$stmt_saidas->close();

// 3. Calcular Balanço
$balanco_final = $total_entradas - $total_saidas;

// 4. Preparar dados para o gráfico (diário)
$dados_grafico = [
    'labels' => range(1, $dias_no_mes),
    'entradas' => array_fill(1, $dias_no_mes, 0),
    'saidas' => array_fill(1, $dias_no_mes, 0),
];

// Entradas por dia
$sql_entradas_dia = "SELECT DAY(data_pagamento) AS dia, SUM(valor) AS total FROM cobrancas WHERE pago = 1 AND data_pagamento BETWEEN ? AND ? GROUP BY dia ORDER BY dia";
$stmt_entradas_dia = $conn->prepare($sql_entradas_dia);
$stmt_entradas_dia->bind_param("ss", $primeiro_dia, $ultimo_dia);
$stmt_entradas_dia->execute();
$result_entradas_dia = $stmt_entradas_dia->get_result();
while ($row = $result_entradas_dia->fetch_assoc()) {
    $dados_grafico['entradas'][$row['dia']] = $row['total'];
}
$stmt_entradas_dia->close();

// Saídas por dia
$sql_saidas_dia = "SELECT DAY(data_vencimento) AS dia, SUM(valor) AS total FROM despesas WHERE pago = 1 AND data_vencimento BETWEEN ? AND ? GROUP BY dia ORDER BY dia";
$stmt_saidas_dia = $conn->prepare($sql_saidas_dia);
$stmt_saidas_dia->bind_param("ss", $primeiro_dia, $ultimo_dia);
$stmt_saidas_dia->execute();
$result_saidas_dia = $stmt_saidas_dia->get_result();
while ($row = $result_saidas_dia->fetch_assoc()) {
    $dados_grafico['saidas'][$row['dia']] = $row['total'];
}
$stmt_saidas_dia->close();

?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar ao Dashboard</a>

<form method="get" action="" class="filter-form">
    <div class="form-group">
        <label for="mes_ano">Selecionar Mês:</label>
        <input type="month" name="mes_ano" id="mes_ano" value="<?php echo $mes_ano; ?>">
        <button type="submit">Gerar Relatório</button>
    </div>
</form>

<div class="summary-container">
    <div class="summary-card">
        <h3>Total de Entradas</h3>
        <p class="income">R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></p>
    </div>
    <div class="summary-card">
        <h3>Total de Saídas</h3>
        <p class="expense">R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></p>
    </div>
    <div class="summary-card">
        <h3>Balanço Final</h3>
        <p class="<?php echo $balanco_final >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
            R$ <?php echo number_format($balanco_final, 2, ',', '.'); ?>
        </p>
    </div>
</div>

<div class="chart-container" style="margin-top: 40px;">
    <canvas id="balancoChart"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('balancoChart').getContext('2d');

    // Converte os dados do PHP para o formato que o Chart.js espera
    const chartData = <?php echo json_encode($dados_grafico); ?>;

    // Precisamos garantir que os arrays de dados comecem do índice 0
    const entradasDiarias = Object.values(chartData.entradas);
    const saidasDiarias = Object.values(chartData.saidas);

    const balancoChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Entradas (R$)',
                    data: entradasDiarias,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Saídas (R$)',
                    data: saidasDiarias,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php
$conn->close();
require_once 'templates/footer.php';
?>