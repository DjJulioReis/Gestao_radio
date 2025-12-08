<?php
require_once 'init.php';
require_once 'email_config2.php';
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

// Totais
$totalAberto = $totalPago = $totalPermuta = 0;
$lista = [];
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $lista[] = $row;
        if($row['pago']) $totalPago += $row['valor'];
        else $totalAberto += $row['valor'];

        $totalPermuta += $row['credito_permuta'];
    }
}
?>
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

                        $status = $row['pago']
                            ? '<span style="color:green;font-weight:bold;">Pago</span>'
                            : '<span style="color:red;font-weight:bold;">Aberto</span>';
                    ?>
                    <tr style="border-bottom:1px solid #ccc;">
                        <td><?=htmlspecialchars($row['empresa']);?></td>
                        <td><?=htmlspecialchars($row['plano_nome']);?></td>
                        <td><?=$row['referencia'];?></td>
                        <td>R$ <?=number_format($valorExibir,2,",",".");?></td>
                        <td>R$ <?=number_format($row['credito_permuta'],2,",",".");?></td>
                        <td><?=$status;?></td>
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
