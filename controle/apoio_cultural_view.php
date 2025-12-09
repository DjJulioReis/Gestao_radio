<?php
require_once 'init.php';
$page_title = "Detalhes do Projeto Cultural";
require_once 'templates/header.php';

// Valida o ID do projeto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: apoios_culturais.php");
    exit();
}
$projeto_id = (int)$_GET['id'];

// Busca os detalhes do projeto
$stmt_projeto = $conn->prepare("SELECT * FROM apoios_culturais WHERE id = ?");
$stmt_projeto->bind_param("i", $projeto_id);
$stmt_projeto->execute();
$result_projeto = $stmt_projeto->get_result();
$projeto = $result_projeto->fetch_assoc();

if (!$projeto) {
    echo "<p>Projeto não encontrado.</p>";
    require_once 'templates/footer.php';
    exit();
}

// Busca os clientes que apoiam este projeto
$stmt_apoiadores = $conn->prepare(
    "SELECT c.id AS cliente_id, c.empresa, ac.valor_doado, ac.forma_anuncio, ac.data_apoio
     FROM apoios_clientes ac
     JOIN clientes c ON ac.cliente_id = c.id
     WHERE ac.apoio_id = ?
     ORDER BY ac.data_apoio DESC"
);
$stmt_apoiadores->bind_param("i", $projeto_id);
$stmt_apoiadores->execute();
$result_apoiadores = $stmt_apoiadores->get_result();
?>

<h1><?php echo htmlspecialchars($projeto['nome_projeto']); ?></h1>
<a href="apoios_culturais.php" class="btn btn-secondary">Voltar para a Lista de Projetos</a>
<hr>

<h3>Detalhes do Projeto</h3>
<p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($projeto['descricao'])); ?></p>
<p><strong>Meta de Arrecadação:</strong> R$ <?php echo number_format($projeto['meta_arrecadacao'], 2, ',', '.'); ?></p>
<p><strong>Período:</strong> <?php echo date("d/m/Y", strtotime($projeto['data_inicio'])); ?> a <?php echo date("d/m/Y", strtotime($projeto['data_fim'])); ?></p>

<h3 class="mt-4">Apoiadores</h3>
<?php if ($result_apoiadores->num_rows > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Cliente (Empresa)</th>
                <th>Valor Doado (R$)</th>
                <th>Forma de Anúncio</th>
                <th>Data do Apoio</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($apoiador = $result_apoiadores->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($apoiador['empresa']); ?></td>
                    <td><?php echo number_format($apoiador['valor_doado'], 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($apoiador['forma_anuncio']); ?></td>
                    <td><?php echo date("d/m/Y H:i", strtotime($apoiador['data_apoio'])); ?></td>
                    <td>
                        <a href="gerar_contrato_apoio.php?apoio_id=<?php echo $projeto_id; ?>&cliente_id=<?php echo $apoiador['cliente_id']; ?>" target="_blank" title="Gerar Contrato PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhum apoiador para este projeto ainda.</p>
<?php endif; ?>

<hr>

<div class="card mt-4">
    <div class="card-header">
        <h3>Adicionar Novo Apoiador</h3>
    </div>
    <div class="card-body">
        <form action="src/apoio_cliente_add_handler.php" method="post">
            <input type="hidden" name="apoio_id" value="<?php echo $projeto_id; ?>">
            <div class="form-group">
                <label for="cliente_id">Selecione o Cliente</label>
                <select name="cliente_id" id="cliente_id" class="form-control" required>
                    <option value="">-- Escolha um cliente --</option>
                    <?php
                    // Busca todos os clientes para o dropdown
                    $result_clientes = $conn->query("SELECT id, empresa FROM clientes WHERE ativo = 1 ORDER BY empresa");
                    while ($cliente = $result_clientes->fetch_assoc()) {
                        echo '<option value="' . $cliente['id'] . '">' . htmlspecialchars($cliente['empresa']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="valor_doado">Valor Doado (R$)</label>
                <input type="number" step="0.01" name="valor_doado" id="valor_doado" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="forma_anuncio">Forma de Anúncio</label>
                <textarea name="forma_anuncio" id="forma_anuncio" rows="3" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Adicionar Apoiador</button>
        </form>
    </div>
</div>

<?php
$stmt_projeto->close();
$stmt_apoiadores->close();
$conn->close();
require_once 'templates/footer.php';
?>
