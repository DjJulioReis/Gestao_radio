<?php
require_once 'init.php';
$page_title = "Adicionar Comercial";
require_once 'templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Buscar clientes para o dropdown
$clientes = $conn->query("SELECT id, empresa FROM clientes ORDER BY empresa");
?>

<h1><?php echo $page_title; ?></h1>
<a href="comerciais.php">Voltar para a Gestão de Comerciais</a>

<form action="src/comercial_add_handler.php" method="post" enctype="multipart/form-data" class="form-container">
    <div class="form-group">
        <label for="cliente_id">Cliente</label>
        <select name="cliente_id" id="cliente_id" required>
            <option value="">-- Selecione o Cliente --</option>
            <?php while($cliente = $clientes->fetch_assoc()): ?>
                <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['empresa']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="identificador_arquivo">Nome do Arquivo do Comercial</label>
        <input type="text" name="identificador_arquivo" id="identificador_arquivo" required placeholder="Ex: comercial_padaria_pao_quente_30s.mp3">
        <small>Digite o nome exato do arquivo de áudio (incluindo .mp3) que está na pasta do aplicativo Windows.</small>
    </div>

    <div class="form-group">
        <label for="duracao">Duração (em segundos)</label>
        <input type="number" name="duracao" id="duracao" required placeholder="Ex: 30">
    </div>

    <div class="form-group">
        <label for="ativo">
            <input type="checkbox" name="ativo" id="ativo" value="1" checked>
            Comercial Ativo
        </label>
    </div>

    <button type="submit">Salvar Comercial</button>
</form>

<?php
$conn->close();
require_once 'templates/footer.php';
?>