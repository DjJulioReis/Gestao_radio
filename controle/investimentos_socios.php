<?php
require_once 'init.php';
$page_title = 'Investimentos dos Sócios';
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Lógica para adicionar/editar/excluir
// (Será implementado em handlers separados)

// Buscar investimentos
$investimentos = $conn->query("
    SELECT i.*, l.nome as locutor_nome
    FROM investimentos_socios i
    JOIN locutores l ON i.locutor_id = l.id
    ORDER BY i.data DESC
");

// Buscar locutores (sócios)
$locutores = $conn->query("SELECT id, nome FROM locutores ORDER BY nome");
?>

<h1><?php echo $page_title; ?></h1>
<a href="dashboard.php">Voltar ao Dashboard</a>

<h2>Adicionar Novo Investimento</h2>
<form action="src/investimento_add_handler.php" method="post" class="form-inline">
    <div class="form-group">
        <label for="locutor_id">Sócio:</label>
        <select name="locutor_id" required>
            <option value="">-- Selecione --</option>
            <?php while ($locutor = $locutores->fetch_assoc()): ?>
                <option value="<?php echo $locutor['id']; ?>"><?php echo htmlspecialchars($locutor['nome']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="valor">Valor:</label>
        <input type="number" name="valor" step="0.01" required>
    </div>
    <div class="form-group">
        <label for="data">Data:</label>
        <input type="date" name="data" required>
    </div>
    <div class="form-group">
        <label for="descricao">Descrição:</label>
        <input type="text" name="descricao" placeholder="Ex: Aporte para equipamento">
    </div>
    <button type="submit">Adicionar</button>
</form>

<hr>

<h2>Investimentos Registrados</h2>
<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Sócio</th>
            <th>Valor</th>
            <th>Descrição</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($investimentos->num_rows > 0): ?>
            <?php while ($investimento = $investimentos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("d/m/Y", strtotime($investimento['data'])); ?></td>
                    <td><?php echo htmlspecialchars($investimento['locutor_nome']); ?></td>
                    <td>R$ <?php echo number_format($investimento['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($investimento['descricao']); ?></td>
                    <td>
                        <?php if (!$investimento['reembolsado']): ?>
                            <a href="src/investimento_reembolsar_handler.php?id=<?php echo $investimento['id']; ?>" onclick="return confirm('Isso registrará uma despesa e abaterá o saldo do sócio. Confirma?');">Reembolsar</a> |
                        <?php else: ?>
                            <span style="color: green;">Reembolsado</span> |
                        <?php endif; ?>
                        <a href="src/investimento_delete_handler.php?id=<?php echo $investimento['id']; ?>" onclick="return confirm('Tem certeza?');">Excluir</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Nenhum investimento registrado.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
