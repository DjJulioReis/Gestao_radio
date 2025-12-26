<?php
// Apenas para execução via linha de comando (CLI)
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.");
}

require_once dirname(__DIR__) . '/init.php';

echo "Iniciando a geração de playlist para o RadioBOSS...\n";

// A data alvo é sempre o dia seguinte, igual ao script de agendamento
$target_date = new DateTime('tomorrow');
$target_date_str = $target_date->format('Y-m-d');
$output_dir = dirname(__DIR__) . '/uploads/playlists_radioboss';
$output_filename = "playlist_{$target_date->format('Ymd')}.m3u";
$output_path = "{$output_dir}/{$output_filename}";

if (!is_dir($output_dir)) {
    mkdir($output_dir, 0775, true);
}

echo "Gerando playlist para a data: {$target_date_str}\n";
echo "Salvando em: {$output_path}\n";

// 1. Buscar agendamentos pendentes para a data alvo, ordenados por horário
$sql = "
    SELECT
        a.horario_programado,
        c.caminho_arquivo
    FROM agendamentos a
    JOIN comerciais c ON a.comercial_id = c.id
    WHERE DATE(a.horario_programado) = ? AND a.status = 'pendente'
    ORDER BY a.horario_programado ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $target_date_str);
$stmt->execute();
$agendamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($agendamentos)) {
    echo "Nenhum agendamento encontrado para a data. Playlist vazia gerada.\n";
    file_put_contents($output_path, ""); // Cria um arquivo vazio
    exit;
}

// 2. Montar o conteúdo do arquivo M3U
$m3u_content = "#EXTM3U\n";
foreach ($agendamentos as $agendamento) {
    // A implementação exata aqui pode precisar de ajustes dependendo de como o RadioBOSS
    // interpreta os comandos de evento. Por enquanto, vamos adicionar o caminho do arquivo.
    // Futuramente, podemos adicionar comandos específicos, como #EXT-X-RADIOBOSS-EVENT-TIME.

    // Assumindo que o caminho no DB é um caminho absoluto ou relativo à raiz do RadioBOSS
    $m3u_content .= $agendamento['caminho_arquivo'] . "\n";
}

// 3. Salvar o arquivo M3U
file_put_contents($output_path, $m3u_content);

echo count($agendamentos) . " comerciais foram adicionados à playlist.\n";
echo "Geração de playlist finalizada com sucesso!\n";

$conn->close();
?>