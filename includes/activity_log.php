<?php
// Records an entry in activity_logs. $conn must be an open mysqli connection.
function logActivity($conn, $user_id, $action, $target_type = null, $target_id = null, $details = null) {
    $query = "INSERT INTO activity_logs (user_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("logActivity: prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param("issis", $user_id, $action, $target_type, $target_id, $details);
    return $stmt->execute();
}
?>
