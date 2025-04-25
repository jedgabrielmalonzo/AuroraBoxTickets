<?php
// Include the database connection
$mysqli = require __DIR__ . "/../../database.php";

// Check if all the required data is provided
if (isset($_POST['id']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email'])) {
    // Sanitize input
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];

    // Prepare an update query
    $sql = "UPDATE user SET firstname=?, lastname=?, email=? WHERE id=?";
    
    // Initialize prepared statement
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sssi", $firstname, $lastname, $email, $id);
        
        // Execute the query
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'User updated successfully!',
                'user' => [
                    'id' => $id,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Error updating user: ' . $stmt->error
            ]);
        }
        
        // Close the statement
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to prepare statement'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required data'
    ]);
}
?>
