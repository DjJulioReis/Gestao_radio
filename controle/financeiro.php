<?php
require_once 'init.php';
require_once 'email_config.php';
require_once 'templates/header.php';


// Apenas admin
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Buscar cobranças
$sql = "
SELECT 
    cb.id AS cobranca_id,
    cb.referencia,
    cb.valor,
    cb.pago,
    cb.data_pagamento,
    cl.id AS cliente_id,
    cl.empresa,
    cl.credito_permuta,
    cl.email,
    p.nome AS plano_nome
FROM cobrancas cb
INNER JOIN clientes cl ON cb.cliente_id = cl.id
INNER JOIN planos p ON cb.plano_id = p.id
ORDER BY cb.pago ASC, cb.referencia DESC
";
$result = $conn->query($sql);
$lista = ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Cálculos de totais otimizados
$totalAberto = $conn->query("SELECT SUM(valor) AS total FROM cobrancas WHERE pago = 0")->fetch_assoc()['total'] ?? 0;
$totalPago = $conn->query("SELECT SUM(valor) AS total FROM cobrancas WHERE pago = 1")->fetch_assoc()['total'] ?? 0;
$totalPermuta = $conn->query("SELECT SUM(credito_permuta) AS total FROM clientes")->fetch_assoc()['total'] ?? 0;
?>
<style>
    .status-pago { background-color: #d4edda; color: #155724; }
    .status-a-receber { background-color: #fff3cd; color: #856404; }
    .status-em-atraso { background-color: #f8d7da; color: #721c24; }
</style>
<div class="container">
    <h2>Financeiro - Cobranças</h2>
<a href="dashboard.php">Voltar para o Inicio</a>
    <div style="margin-bottom:20px;">
        <strong>Total Aberto:</strong> R$ <?=number_format($totalAberto,2,",",".");?> |
        <strong>Total Pago:</strong> R$ <?=number_format($totalPago,2,",",".");?> |
        <strong>Total Permuta:</strong> R$ <?=number_format($totalPermuta,2,",",".");?>
    </div>

    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background:#3498db; color:white;">
                <th>Cliente</th>
                <th>Plano</th>
                <th>Referência</th>
                <th>Valor</th>
                <th>Permuta</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($lista) > 0): ?>
                <?php foreach($lista as $row): ?>
                    <?php
                        $valorExibir = $row['valor'];
                        $permuta = $row['credito_permuta'];
                        if ($permuta > 0) {
                            if ($permuta >= $valorExibir) {
                                $valorExibir = 0;
                            } else {
                                $valorExibir -= $permuta;
                            }
                        }

                        $status_classe = '';
                        $status_texto = '';
                        $hoje = new DateTime();
                        $data_referencia = new DateTime($row['referencia'] . '-01');
                        // Vencimento no dia 10 do mês de referência
                        $data_vencimento = new DateTime($data_referencia->format('Y-m-10'));

                        if ($row['pago']) {
                            $status_classe = 'status-pago';
                            $status_texto = 'Pago';
                        } elseif ($hoje > $data_vencimento) {
                            $status_classe = 'status-em-atraso';
                            $status_texto = 'Em Atraso';
                        } else {
                            $status_classe = 'status-a-receber';
                            $status_texto = 'A Receber';
                        }
                    ?>
                    <tr class="<?=$status_classe;?>" style="border-bottom:1px solid #ccc;">
                        <td><?=htmlspecialchars($row['empresa']);?></td>
                        <td><?=htmlspecialchars($row['plano_nome']);?></td>
                        <td><?=$row['referencia'];?></td>
                        <td>R$ <?=number_format($valorExibir,2,",",".");?></td>
                        <td>R$ <?=number_format($row['credito_permuta'],2,",",".");?></td>
                        <td><?=$status_texto;?></td>
                        <td>
                            <?php if(!$row['pago']): ?>
                                <a href="quitar.php?id=<?=$row['cobranca_id'];?>" 
                                   style="padding:5px 10px; background:#2ecc71; color:white; text-decoration:none; border-radius:4px;">
                                   Quitar
                                </a>
                            <?php else: ?>
                                <span style="color:grey;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;">Nenhuma cobrança encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
