<?php
require_once 'init.php';

// Redireciona para o login se não estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema Rádio FM</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8em;
        }
        .header a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1em;
            background-color: #e74c3c;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .header a:hover {
            background-color: #c0392b;
        }
        .container {
            padding: 40px;
        }
        .welcome-message {
            margin-bottom: 40px;
        }
        .welcome-message h2 {
            font-weight: 300;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .card h3 {
            margin-top: 0;
            color: #3498db;
        }
        .card p {
            font-size: 0.9em;
            color: #7f8c8d;
        }
        .card a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .card a:hover {
            background-color: #2980b9;
        }
        .reports-section .card h3 { color: #2ecc71; }
        .reports-section .card a { background-color: #2ecc71; }
        .reports-section .card a:hover { background-color: #27ae60; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema de Gestão - Rádio FM</h1>
        <a href="src/logout.php">Sair</a>
    </div>

    <div class="container">
        <div class="welcome-message">
            <h2>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</h2>
            <p>Seu nível de acesso é: <strong><?php echo htmlspecialchars($_SESSION['user_level']); ?></strong>. Use os links abaixo para gerenciar o sistema.</p>
        </div>

        <h2>Cadastros e Gestão</h2>
        <div class="grid-container">
            <div class="card">
                <h3>Clientes</h3>
                <p>Gerencie os clientes e parceiros comerciais da rádio.</p>
                <a href="clientes.php">Acessar</a>
            </div>
            <div class="card">
                <h3>Planos e Pacotes</h3>
                <p>Crie e edite os planos de anúncios disponíveis.</p>
                <a href="planos.php">Acessar</a>
            </div>
            <div class="card">
                <h3>Despesas</h3>
                <p>Controle as despesas fixas e variáveis da rádio.</p>
                <a href="despesas.php">Acessar</a>
            </div>
            <div class="card">
                <h3>Colaboradores</h3>
                <p>Gerencie os colaboradores e suas funções na rádio.</p>
                <a href="colaboradores.php">Acessar</a>
            </div>
            <div class="card">
                <h3>Sócios</h3>
                <p>Gerencie o status de reinvestimento e os investimentos dos sócios.</p>
                <a href="socios.php">Acessar</a>
            </div>
             <div class="card">
                <h3>Associar Clientes</h3>
                <p>Associe clientes a colaboradores para o cálculo de comissões.</p>
                <a href="cliente_colaboradores.php">Acessar</a>
            </div>
            <div class="card">
            <h3>Financeiro</h3>
            <p>Visualize e gerencie cobranças, débitos e pagamentos de clientes.</p>
            <a href="financeiro.php">Acessar</a>
            </div>
            <div class="card">
                <h3>Contratos</h3>
                <p>Gerencie os contratos comerciais com clientes e planos.</p>
                <a href="contratos.php">Acessar</a>
            </div>
            <div class="card">
                <h3>Apoios Culturais</h3>
                <p>Gerencie projetos culturais e seus respectivos apoiadores.</p>
                <a href="apoios_culturais.php">Acessar</a>
            </div>
            <div class="card">
                <h3>Investimentos de Sócios</h3>
                <p>Gerencie os investimentos externos dos sócios.</p>
                <a href="investimentos_socios.php">Acessar</a>
            </div>
        </div>
        

        <h2 style="margin-top: 40px;">Relatórios</h2>
        <div class="grid-container reports-section">
            <div class="card">
                <h3>Relatório Financeiro</h3>
                <p>Visualize o balanço de entradas, saídas e lucro.</p>
                <a href="relatorio_financeiro.php">Gerar</a>
            </div>
            <div class="card">
                <h3>Relatório de Parcerias</h3>
                <p>Veja todos os contratos ativos e informações dos clientes.</p>
                <a href="relatorio_parcerias.php">Gerar</a>
            </div>
            <div class="card">
                <h3>Relatório de Locutor</h3>
                <p>Visualize as comissões e clientes de um locutor.</p>
                <a href="relatorio_locutor.php">Gerar</a>
            </div>
            <div class="card">
                <h3>Relatório de Débitos</h3>
                <p>Liste todos os clientes com faturas pendentes.</p>
                <a href="relatorio_debitos.php">Gerar</a>
            </div>
            <div class="card">
                <h3>Apoio Cultural</h3>
                <p>Relatório de projetos e seus apoiadores.</p>
                <a href="relatorio_apoio_cultural.php">Gerar</a>
            </div>
        </div>
    </div>
</body>
</html>
