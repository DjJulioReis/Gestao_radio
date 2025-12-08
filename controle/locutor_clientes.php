<?php 
require_once 'init.php';
$page_title = "Associar Clientes a Locutores";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Obter todos os locutores e clientes
$locutores = $conn->query("SELECT id, nome FROM locutores ORDER BY nome");
$clientes = $conn->query("SELECT id, empresa FROM clientes ORDER BY empresa");

// Obter associações existentes
$associacoes = [];
$result_assoc = $conn->query("SELECT cliente_id, locutor_id FROM clientes_locutores");
if ($result_assoc->num_rows > 0) {
    while ($row = $result_assoc->fetch_assoc()) {
        $associacoes[$row['cliente_id']] = $row['locutor_id'];
    }
}
?>

<h1><?php echo $page_title; ?></h1>
<p>Associe um locutor a cada cliente para calcular a comissão de 50% sobre os contratos.</p>

<form action="src/locutor_clientes_handler.php" method="post">
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Locutor Responsável</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($cliente = $clientes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cliente['empresa']); ?></td>
                    <td>
                        <select name="associacao[<?php echo $cliente['id']; ?>]">
                            <option value="">Nenhum</option>
                            <?php
                            // Resetar ponteiro do locutores
                            $locutores->data_seek(0);
                            while ($locutor = $locutores->fetch_assoc()) {
                                $selected = (isset($associacoes[$cliente['id']]) && $associacoes[$cliente['id']] == $locutor['id'])
                                    ? 'selected'
                                    : '';
                                echo "<option value='{$locutor['id']}' {$selected}>" 
                                     . htmlspecialchars($locutor['nome']) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <button type="submit">Salvar Associações</button>
</form>

<hr><br>

<h2>Lista de Locutores e seus Clientes</h2>

<?php
$sql = "
    SELECT 
        l.nome AS locutor,
        c.empresa AS cliente,
        c.data_vencimento,
        c.ativo,
        p.nome AS plano,
        p.preco AS valor_plano,
        (p.preco * 0.5) AS comissao,
        c.data_cadastro
    FROM clientes_locutores cl
    JOIN clientes c ON c.id = cl.cliente_id
    JOIN locutores l ON l.id = cl.locutor_id
    JOIN planos p ON p.id = c.plano_id
    ORDER BY l.nome, c.empresa
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {

    echo "<table border='1' cellpadding='5' cellspacing='0'>
            <thead>
                <tr>
                    <th>Locutor</th>
                    <th>Cliente</th>
                    <th>Plano</th>
                    <th>Valor</th>
                    <th>Comissão</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
        ";

    while ($row = $result->fetch_assoc()) {

        // DIA DO VENCIMENTO
        $venc = (int)$row['data_vencimento'];

        // GERAR A DATA DE VENCIMENTO DESTE MÊS
        $hoje = new DateTime();
        $ano = $hoje->format("Y");
        $mes = $hoje->format("m");

        // Ajustar se o dia não existir (ex: fevereiro)
        $dia_valido = min($venc, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

        $data_vencimento = DateTime::createFromFormat("Y-m-d", "$ano-$mes-$dia_valido");

        // Se já passou, gera do próximo mês
        if ($data_vencimento < $hoje) {
            $mes++;
            if ($mes > 12) {
                $mes = 1;
                $ano++;
            }
            $dia_valido = min($venc, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));
            $data_vencimento = DateTime::createFromFormat("Y-m-d", "$ano-$mes-$dia_valido");
        }

        // CALCULAR STATUS
        $dias_restantes = (int)$hoje->diff($data_vencimento)->format("%r%a");

        if ($row['ativo'] == 0) {
            $status = "<span style='color:gray;font-weight:bold;'>INATIVO</span>";
        } elseif ($dias_restantes < 0) {
            $status = "<span style='color:red;font-weight:bold;'>ATRASADO</span>";
        } elseif ($dias_restantes == 0) {
            $status = "<span style='color:orange;font-weight:bold;'>VENCE HOJE</span>";
        } elseif ($dias_restantes <= 5) {
            $status = "<span style='color:#c49a00;font-weight:bold;'>VENCE EM BREVE</span>";
        } else {
            $status = "<span style='color:green;font-weight:bold;'>OK</span>";
        }

        echo "
        <tr>
            <td>{$row['locutor']}</td>
            <td>{$row['cliente']}</td>
            <td>{$row['plano']}</td>
            <td>R$ " . number_format($row['valor_plano'], 2, ',', '.') . "</td>
            <td>R$ " . number_format($row['comissao'], 2, ',', '.') . "</td>
            <td>{$row['data_vencimento']}</td>
            <td>{$status}</td>
        </tr>";
    }

    echo "</tbody></table>";

} else {
    echo "<p>Nenhuma associação encontrada.</p>";
}

$conn->close();
require_once 'templates/footer.php';
?>
