<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'init.php';
$page_title = "Associar Clientes a Colaboradores";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Obter todos os colaboradores (locutores) e clientes
$colaboradores = $conn->query("SELECT id, nome FROM colaboradores WHERE funcao IN ('locutor', 'socio_locutor') ORDER BY nome");
$clientes = $conn->query("SELECT id, empresa FROM clientes ORDER BY empresa");

// Obter associações existentes
$associacoes = [];
$result_assoc = $conn->query("SELECT cliente_id, colaborador_id, percentual_comissao FROM cliente_colaboradores");
if ($result_assoc->num_rows > 0) {
    while ($row = $result_assoc->fetch_assoc()) {
        $associacoes[$row['cliente_id']] = [
            'colaborador_id' => $row['colaborador_id'],
            'percentual_comissao' => $row['percentual_comissao']
        ];
    }
}
?>

<h1><?php echo $page_title; ?></h1>
<p>Associe um colaborador a cada cliente e defina o seu percentual de comissão sobre os contratos.</p>

<form action="src/cliente_colaboradores_handler.php" method="post">
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Colaborador Responsável</th>
                <th>Comissão (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($cliente = $clientes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cliente['empresa']); ?></td>
                    <td>
                        <select name="associacao[<?php echo $cliente['id']; ?>][colaborador_id]">
                            <option value="">Nenhum</option>
                            <?php
                            $colaboradores->data_seek(0);
                            while ($colaborador = $colaboradores->fetch_assoc()) {
                                $selected = (isset($associacoes[$cliente['id']]) && $associacoes[$cliente['id']]['colaborador_id'] == $colaborador['id']) ? 'selected' : '';
                                echo "<option value='{$colaborador['id']}' {$selected}>" . htmlspecialchars($colaborador['nome']) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="associacao[<?php echo $cliente['id']; ?>][percentual_comissao]"
                               value="<?php echo $associacoes[$cliente['id']]['percentual_comissao'] ?? '50.00'; ?>"
                               placeholder="Ex: 50.00">
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <button type="submit">Salvar Associações</button>
</form>

<hr><br>

<h2>Lista de Colaboradores e seus Clientes</h2>

<?php
$sql = "
    SELECT
        col.nome AS colaborador,
        c.empresa AS cliente,
        c.ativo,
        ct.identificacao AS contrato_identificacao,
        ct.data_fim AS data_vencimento,
        ct.valor AS valor_contrato,
        cc.percentual_comissao,
        (ct.valor * cc.percentual_comissao / 100) AS comissao_calculada
    FROM cliente_colaboradores cc
    JOIN clientes c ON c.id = cc.cliente_id
    JOIN colaboradores col ON col.id = cc.colaborador_id
    LEFT JOIN contratos ct ON ct.cliente_id = c.id
    WHERE col.funcao IN ('locutor', 'socio_locutor')
    ORDER BY col.nome, c.empresa, ct.data_fim DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $current_colaborador = null;
    $total_comissao_colaborador = 0;

    while ($row = $result->fetch_assoc()) {
        if ($row['colaborador'] !== $current_colaborador) {
            if ($current_colaborador !== null) {
                // Imprime o total do colaborador anterior
                echo "<tr><td colspan='4' style='text-align: right; font-weight: bold;'>Total Comissão:</td><td colspan='2' style='font-weight: bold;'>R$ " . number_format($total_comissao_colaborador, 2, ',', '.') . "</td></tr>";
                echo "</tbody></table><br>";
            }
            $current_colaborador = $row['colaborador'];
            $total_comissao_colaborador = 0; // Reseta o total para o novo colaborador
            echo "<h3>" . htmlspecialchars($current_colaborador) . "</h3>";
            echo "<table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contrato</th>
                            <th>Valor</th>
                            <th>Comissão (%)</th>
                            <th>Comissão (R$)</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>";
        }

        $total_comissao_colaborador += $row['comissao_calculada'] ?? 0;

        $status = '';
        if ($row['ativo'] == 0) {
            $status = "<span style='color:gray;font-weight:bold;'>INATIVO</span>";
        } elseif (empty($row['contrato_identificacao'])) {
            $status = "<span style='color:orange;font-weight:bold;'>SEM CONTRATO</span>";
        } else {
            $status = "<span style='color:green;font-weight:bold;'>ATIVO</span>";
        }

        $valor_contrato = $row['valor_contrato'] ?? 0;
        $comissao_calculada = $row['comissao_calculada'] ?? 0;
        $percentual_comissao = $row['percentual_comissao'] ?? 0;

        echo "
        <tr>
            <td>" . htmlspecialchars($row['cliente']) . "</td>
            <td>" . htmlspecialchars($row['contrato_identificacao'] ?? 'N/A') . "</td>
            <td>R$ " . number_format($valor_contrato, 2, ',', '.') . "</td>
            <td>" . number_format($percentual_comissao, 2, ',', '.') . "%</td>
            <td>R$ " . number_format($comissao_calculada, 2, ',', '.') . "</td>
            <td>" . ($row['data_vencimento'] ? date("d/m/Y", strtotime($row['data_vencimento'])) : 'N/A') . "</td>
            <td>{$status}</td>
        </tr>";
    }
    // Imprime o total do último colaborador
    echo "<tr><td colspan='4' style='text-align: right; font-weight: bold;'>Total Comissão:</td><td colspan='2' style='font-weight: bold;'>R$ " . number_format($total_comissao_colaborador, 2, ',', '.') . "</td></tr>";
    echo "</tbody></table>";

} else {
    echo "<p>Nenhuma associação encontrada.</p>";
}

$conn->close();
require_once 'templates/footer.php';
?>
