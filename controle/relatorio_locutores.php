<?php
require_once 'init.php';
$page_title = "Relatório de Comissões dos Locutores";
require_once 'templates/header.php';

// Define o mês e ano para o filtro, padrão para o mês atual
$mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m');
$ano = date('Y', strtotime($mes_ano));
$mes = date('m', strtotime($mes_ano));

// SQL para buscar a receita de cada locutor no mês
$sql = "
    SELECT
        l.nome AS nome_locutor,
        SUM(p.preco) AS receita_total,
        (SUM(p.preco) * 0.5) AS comissao
    FROM locutores l
    JOIN clientes_locutores cl ON l.id = cl.locutor_id
    JOIN contratos ct ON cl.cliente_id = ct.cliente_id
    JOIN planos p ON ct.plano_id = p.id
    WHERE YEAR(ct.data_inicio) <= ? AND MONTH(ct.data_inicio) <= ?
      AND YEAR(ct.data_fim) >= ? AND MONTH(ct.data_fim) >= ?
    GROUP BY l.id, l.nome
    ORDER BY l.nome
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $ano, $mes, $ano, $mes);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1><?php echo $page_title; ?></h1>

<form method="get" action="">
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
            <th>Locutor</th>
            <th>Receita Gerada (R$)</th>
            <th>Comissão (50%) (R$)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome_locutor']); ?></td>
                    <td><?php echo number_format($row['receita_total'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($row['comissao'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">Nenhuma comissão gerada para este mês.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/templates/footer.php';
?>
