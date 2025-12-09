<?php
function handle_receipt_upload($file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'Nenhum arquivo enviado ou erro no upload.'
        ];
    }

    $year = date('Y');
    $month = date('m');
    $target_dir = __DIR__ . "/../uploads/{$year}/{$month}/";

    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            return [
                'success' => false,
                'error' => 'Falha ao criar o diretório de uploads.'
            ];
        }
    }

    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png'];

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_mime_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_extension, $allowed_extensions) || !in_array($file_mime_type, $allowed_mime_types)) {
        return [
            'success' => false,
            'error' => 'Tipo de arquivo inválido. Apenas PDF, JPG e PNG são permitidos.'
        ];
    }

    $unique_filename = uniqid('', true) . '.' . $file_extension;
    $target_file = $target_dir . $unique_filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return [
            'success' => true,
            'filepath' => "uploads/{$year}/{$month}/" . $unique_filename
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Falha ao mover o arquivo enviado.'
        ];
    }
}
?>