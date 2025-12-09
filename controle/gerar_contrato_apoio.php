<?php
require_once 'init.php';
require_once 'lib/fpdf/fpdf.php';

// Apenas administradores podem gerar contratos
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Valida os IDs
if (!isset($_GET['apoio_id']) || !isset($_GET['cliente_id'])) {
    die("IDs de apoio ou cliente não fornecidos.");
}
$apoio_id = (int)$_GET['apoio_id'];
$cliente_id = (int)$_GET['cliente_id'];

// Busca todas as informações necessárias
$query = "
    SELECT
        ac.valor_doado,
        ac.forma_anuncio,
        ac.data_apoio,
        ap.nome_projeto,
        cl.empresa AS cliente_empresa,
        cl.cnpj_cpf AS cliente_cnpj,
        cl.endereco AS cliente_endereco
    FROM apoios_clientes AS ac
    JOIN apoios_culturais AS ap ON ac.apoio_id = ap.id
    JOIN clientes AS cl ON ac.cliente_id = cl.id
    WHERE ac.apoio_id = ? AND ac.cliente_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $apoio_id, $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Não foi possível encontrar os dados para gerar o contrato.");
}

// Inicia a geração do PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Título
$pdf->Cell(0, 10, 'CONTRATO DE APOIO CULTURAL', 0, 1, 'C');
$pdf->Ln(10);

// Corpo do Contrato
$pdf->SetFont('Arial', '', 12);

$texto_contrato = "
Este contrato de apoio cultural é celebrado entre a Rádio Comunitária Nova FM e a empresa ".utf8_decode($data['cliente_empresa']).", inscrita no CNPJ/CPF sob o nº ".utf8_decode($data['cliente_cnpj']).", com endereço em ".utf8_decode($data['cliente_endereco']).".

A empresa acima citada concorda em apoiar o projeto cultural \"".utf8_decode($data['nome_projeto'])."\", organizado por esta rádio, com uma doação no valor de R$ ".number_format($data['valor_doado'], 2, ',', '.').".

Como contrapartida, a Rádio Nova FM se compromete a realizar a seguinte forma de anúncio e agradecimento:
".utf8_decode($data['forma_anuncio'])."

Este apoio foi firmado em ".date('d/m/Y', strtotime($data['data_apoio'])).".
";

$pdf->MultiCell(0, 10, $texto_contrato);
$pdf->Ln(20);

// Assinaturas
$pdf->Cell(0, 10, '________________________________________', 0, 1, 'C');
$pdf->Cell(0, 5, 'Assinatura do Responsável pela Rádio', 0, 1, 'C');
$pdf->Ln(10);
$pdf->Cell(0, 10, '________________________________________', 0, 1, 'C');
$pdf->Cell(0, 5, "Assinatura do Responsável por ".utf8_decode($data['cliente_empresa']), 0, 1, 'C');


// Saída do PDF
$pdf->Output('D', 'Contrato_Apoio_'.preg_replace('/[^A-Za-z0-9\-]/', '', $data['cliente_empresa']).'.pdf');

$stmt->close();
$conn->close();
exit();
