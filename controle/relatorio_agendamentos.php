<?php
require_once 'init.php';
$page_title = "Relatório de Agendamentos";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Filtros
$data_filtro = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$cliente_id_filtro = filter_input(INPUT_GET, 'cliente_id', FILTER_VALIDATE_INT);

// Buscar clientes para o dropdown
$clientes = $conn->query("SELECT id, empresa FROM clientes ORDER BY empresa");

// Construir a query base
$sql = "
    SELECT
        a.horario_programado,
        a.status,
        c.nome_arquivo,
        cl.empresa as nome_cliente
    FROM agendamentos a
    JOIN comerciais c ON a.comercial_id = c.id
    JOIN clientes cl ON c.cliente_id = cl.id
    WHERE DATE(a.horario_programado) = ?
";

// Adicionar filtro de cliente se selecionado
if ($cliente_id_filtro) {
    $sql .= " AND c.cliente_id = ?";
}

$sql .= " ORDER BY a.horario_programado ASC";

$stmt = $conn->prepare($sql);

// Bind dos parâmetros
if ($cliente_id_filtro) {
    $stmt->bind_param("si", $data_filtro, $cliente_id_filtro);
} else {
    $stmt->bind_param("s", $data_filtro);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar ao Dashboard</a>

<form method="get" class="filter-form">
    <div class="form-group">
        <label for="data">Data:</label>
        <input type="date" name="data" id="data" value="<?php echo $data_filtro; ?>">
    </div>
    <div class="form-group">
        <label for="cliente_id">Cliente:</label>
        <select name="cliente_id" id="cliente_id">
            <option value="">-- Todos os Clientes --</option>
            <?php while ($cliente = $clientes->fetch_assoc()): ?>
                <option value="<?php echo $cliente['id']; ?>" <?php echo ($cliente['id'] == $cliente_id_filtro) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cliente['empresa']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit">Filtrar</button>
</form>

<table>
    <thead>
        <tr>
            <th>Horário Programado</th>
            <th>Cliente</th>
            <th>Comercial</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("d/m/Y H:i:s", strtotime($row['horario_programado'])); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_arquivo']); ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Nenhum agendamento encontrado para os filtros selecionados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer.php';
?>