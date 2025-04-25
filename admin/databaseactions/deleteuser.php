<?php
require __DIR__ . "/../../database.php";

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $sql = "DELETE FROM user WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting user.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement.']);
    }
    
    $stmt->close();
}
?>
