<?php
function log_action($user_id, $action, $target, $target_id) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, target, target_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $action, $target, $target_id);
    $stmt->execute();
    $stmt->close();
}
?>