<?php
/**
 * Função genérica para lidar com uploads de arquivos.
 *
 * @param array $file O array do arquivo de $_FILES.
 * @param string $relative_target_dir O diretório de destino relativo à pasta 'controle'.
 * @param array $allowed_extensions Extensões de arquivo permitidas.
 * @param array $allowed_mime_types Tipos MIME permitidos.
 * @return array Um array com o resultado do upload.
 */
function uploadArquivo($file, $relative_target_dir, $allowed_extensions, $allowed_mime_types) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Nenhum arquivo enviado ou erro no upload. Código: ' . $file['error']];
    }

    // Garante que o diretório base de uploads exista
    $base_upload_path = dirname(__DIR__) . '/uploads';
    if (!is_dir($base_upload_path)) {
        mkdir($base_upload_path, 0775, true);
    }

    $target_dir_full_path = $base_upload_path . '/' . ltrim($relative_target_dir, '/');

    if (!is_dir($target_dir_full_path)) {
        if (!mkdir($target_dir_full_path, 0775, true)) {
            return ['error' => 'Falha ao criar o diretório de destino.'];
        }
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_mime_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_extension, $allowed_extensions) || !in_array($file_mime_type, $allowed_mime_types)) {
        return ['error' => 'Tipo de arquivo inválido. Permitidos: ' . implode(', ', $allowed_extensions)];
    }

    // Usar o nome original do arquivo, sanitizado, para facilitar a identificação
    $sanitized_filename = preg_replace("/[^a-zA-Z0-9-_\.]/", "", basename($file['name']));
    $unique_prefix = uniqid('', true) . '_';
    $target_file = $target_dir_full_path . '/' . $unique_prefix . $sanitized_filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return [
            'filepath' => '/uploads/' . ltrim($relative_target_dir, '/') . '/' . $unique_prefix . $sanitized_filename,
            'filename' => $sanitized_filename,
            'error' => null
        ];
    } else {
        return ['error' => 'Falha ao mover o arquivo enviado.'];
    }
}

/**
 * Wrapper para manter a compatibilidade com o upload de recibos de despesas.
 */
function handle_receipt_upload($file) {
    $year = date('Y');
    $month = date('m');
    $relative_dir = "recibos/{$year}/{$month}";
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png'];

    $result = uploadArquivo($file, $relative_dir, $allowed_extensions, $allowed_mime_types);

    if ($result['error']) {
        return ['success' => false, 'error' => $result['error']];
    } else {
        return ['success' => true, 'filepath' => $result['filepath']];
    }
}
?>