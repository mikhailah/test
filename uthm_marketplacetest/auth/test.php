<?php
include("../config/database.php");

echo "Database connected successfully!<br>";

// Test query
$sql = "SELECT * FROM pengguna WHERE emel = 'ai240113@student.uthm.edu.my'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if($user) {
    echo "User found: " . $user['nama_penuh'] . "<br>";
    echo "Role: " . $user['peranan'] . "<br>";
    echo "Password hash: " . $user['kata_laluan'] . "<br>";
    
    // Test password verify
    $test_password = "password";
    if(password_verify($test_password, $user['kata_laluan'])) {
        echo "Password 'password' is CORRECT!<br>";
    } else {
        echo "Password 'password' is WRONG!<br>";
    }
} else {
    echo "User not found!";
}
?>