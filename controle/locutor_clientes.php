<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
        c.ativo,
        p.nome AS plano,
        ct.data_fim AS data_vencimento,
        p.preco AS valor_plano,
        (p.preco * 0.5) AS comissao
    FROM clientes_locutores cl
    JOIN clientes c ON c.id = cl.cliente_id
    JOIN locutores l ON l.id = cl.locutor_id
    LEFT JOIN contratos ct ON ct.cliente_id = c.id AND ct.data_fim >= CURDATE()
    LEFT JOIN planos p ON p.id = ct.plano_id
    ORDER BY l.nome, c.empresa, ct.data_fim DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {

    $current_locutor = null;

    echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse: collapse;'>";

    while ($row = $result->fetch_assoc()) {
        if ($row['locutor'] !== $current_locutor) {
            if ($current_locutor !== null) {
                echo "</tbody></table><br>"; // Fecha a tabela anterior
            }
            $current_locutor = $row['locutor'];
            echo "<h3>" . htmlspecialchars($current_locutor) . "</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse: collapse;'>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Plano</th>
                            <th>Valor</th>
                            <th>Comissão</th>
                            <th>Vencimento do Contrato</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>";
        }

        // Lógica de Status Simplificada
        $status = '';
        if ($row['ativo'] == 0) {
            $status = "<span style='color:gray;font-weight:bold;'>INATIVO</span>";
        } elseif (empty($row['plano']) || is_null($row['plano'])) {
            $status = "<span style='color:orange;font-weight:bold;'>SEM CONTRATO</span>";
        } else {
            $status = "<span style='color:green;font-weight:bold;'>ATIVO</span>";
        }

        // Formatação de valores
        $valor_plano = $row['valor_plano'] ?? 0;
        $comissao = $row['comissao'] ?? 0;

        echo "
        <tr>
            <td>" . htmlspecialchars($row['cliente']) . "</td>
            <td>" . htmlspecialchars($row['plano'] ?? 'N/A') . "</td>
            <td>R$ " . number_format($valor_plano, 2, ',', '.') . "</td>
            <td>R$ " . number_format($comissao, 2, ',', '.') . "</td>
            <td>" . ($row['data_vencimento'] ? date("d/m/Y", strtotime($row['data_vencimento'])) : 'N/A') . "</td>
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
