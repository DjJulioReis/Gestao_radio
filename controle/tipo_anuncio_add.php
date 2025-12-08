$page_title = "Adicionar Tipo de Anúncio";
require_once __DIR__ . '/templates/header.php';

// Apenas administradores
if ($_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<h1><?php echo $page_title; ?></h1>
<form action="src/tipo_anuncio_add_handler.php" method="post">
    <div class="form-group">
        <label for="nome">Nome do Tipo</label>
        <input type="text" name="nome" id="nome" required>
    </div>
    <button type="submit">Salvar</button>
    <a href="tipos_anuncio.php" class="cancel-link">Cancelar</a>
</form>

<?php
require_once __DIR__ . '/templates/footer.php';
?>
