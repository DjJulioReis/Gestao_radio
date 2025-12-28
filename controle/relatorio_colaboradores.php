<?php
require_once 'init.php';
$page_title = "Relatório de Comissões dos Colaboradores";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Define o mês e ano para o filtro, padrão para o mês atual
$mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m');
$primeiro_dia_mes = $mes_ano . '-01';
$ultimo_dia_mes = date('Y-m-t', strtotime($primeiro_dia_mes));

// SQL para buscar a receita e comissão de cada colaborador no mês
$sql = "
    SELECT
        col.nome AS nome_colaborador,
        SUM(ct.valor) AS total_faturado,
        SUM(ct.valor * cc.percentual_comissao / 100) AS comissao_total
    FROM colaboradores col
    JOIN cliente_colaboradores cc ON col.id = cc.colaborador_id
    JOIN contratos ct ON cc.cliente_id = ct.cliente_id
    WHERE col.funcao IN ('locutor', 'socio_locutor')
      AND ct.data_inicio <= ?
      AND ct.data_fim >= ?
    GROUP BY col.id, col.nome
    ORDER BY col.nome
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $ultimo_dia_mes, $primeiro_dia_mes);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">voltar ao inicio</a>

<form method="get" action="" class="filter-form">
    <div class="form-group">
        <label for="mes_ano">Selecionar Mês:</label>
        <input type="month" name="mes_ano" id="mes_ano" value="<?php echo $mes_ano; ?>">
        <button type="submit">Gerar Relatório</button>
    </div>
</form>

<h3>Comissões para <?php echo date('m/Y', strtotime($mes_ano)); ?></h3>
<table>
    <thead>
        <tr>
            <th>Colaborador</th>
            <th>Total Faturado (R$)</th>
            <th>Comissão (R$)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php
            $total_geral_faturado = 0;
            $total_geral_comissao = 0;
            while($row = $result->fetch_assoc()):
                $total_geral_faturado += $row['total_faturado'];
                $total_geral_comissao += $row['comissao_total'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome_colaborador']); ?></td>
                    <td><?php echo number_format($row['total_faturado'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($row['comissao_total'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Total Geral</th>
                <th>R$ <?php echo number_format($total_geral_faturado, 2, ',', '.'); ?></th>
                <th>R$ <?php echo number_format($total_geral_comissao, 2, ',', '.'); ?></th>
            </tr>
        </tfoot>
        <?php else: ?>
        <tbody>
            <tr>
                <td colspan="3">Nenhuma comissão gerada para este mês.</td>
            </tr>
        </tbody>
        <?php endif; ?>
</table>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
