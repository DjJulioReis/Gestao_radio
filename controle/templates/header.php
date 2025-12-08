<?php
// O init.php já cuida do session_start()
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    // O redirecionamento agora é relativo
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Sistema Rádio FM' : 'Sistema Rádio FM'; ?></title>
    <!-- O caminho para o CSS é relativo à raiz do projeto -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="header">
        <h1><a href="dashboard.php" style="color: #ecf0f1; text-decoration: none;">Sistema de Gestão - Rádio FM</a></h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- O logout aponta para a pasta src -->
            <a href="src/logout.php" class="logout-btn">Sair</a>
        <?php endif; ?>
    </div>
    <div class="container">
