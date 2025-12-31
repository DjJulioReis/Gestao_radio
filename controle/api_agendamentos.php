<?php
// Define o cabeçalho como JSON para que o aplicativo cliente entenda a resposta
header('Content-Type: application/json');

require_once 'init.php';

// Permite que o aplicativo Windows acesse esta API (CORS)
header("Access-Control-Allow-Origin: *");

// Validação da data recebida via GET
$data_filtro = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$date_obj = DateTime::createFromFormat('Y-m-d', $data_filtro);
if (!$date_obj || $date_obj->format('Y-m-d') !== $data_filtro) {
    echo json_encode(['error' => 'Formato de data inválido. Use AAAA-MM-DD.']);
    exit;
}

// Prepara a resposta
$response = [
    'data_programacao' => $data_filtro,
    'agendamentos' => []
];

// Buscar agendamentos pendentes para a data alvo
$sql = "
    SELECT
        a.horario_programado,
        c.identificador_arquivo,
        c.duracao
    FROM agendamentos a
    JOIN comerciais c ON a.comercial_id = c.id
    WHERE DATE(a.horario_programado) = ? AND a.status = 'pendente'
    ORDER BY a.horario_programado ASC
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $data_filtro);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['agendamentos'][] = [
            'horario' => $row['horario_programado'],
            'arquivo' => $row['identificador_arquivo'],
            'duracao' => (int) $row['duracao']
        ];
    }

    $stmt->close();
} else {
    // Em caso de erro, retorna um erro JSON
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao preparar a consulta ao banco de dados.']);
    exit;
}

$conn->close();

// Retorna a resposta em formato JSON
echo json_encode($response);
?>