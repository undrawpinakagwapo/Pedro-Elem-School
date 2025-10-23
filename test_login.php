<?php

// 1. Put the password you are trying to log in with here
$my_password = 'admin123';

// 2. Paste the long hash you copied from phpMyAdmin here
$hash_from_db = '$2y$10$lPldI/atWLHXQ6AKBRHVfuYNlwMtrBvJyJy1oAuK15Nzc0.gsPMbW';


echo "<h2>Login Test</h2>";
echo "<b>Password to check:</b> " . htmlspecialchars($my_password) . "<br>";
echo "<b>Hash from database:</b> " . htmlspecialchars($hash_from_db) . "<br><br>";

if (password_verify($my_password, $hash_from_db)) {
    echo '<h3 style="color:green;">✅ SUCCESS: The password matches the hash!</h3>';
} else {
    echo '<h3 style="color:red;">❌ FAILURE: The password DOES NOT match the hash.</h3>';
    echo "<br><b>Possible reasons:</b><br>";
    echo "1. The hash in the database is incorrect (maybe it was copied wrong, is still truncated, or has extra spaces).<br>";
    echo "2. The password you typed in `\$my_password` is not the one used to create the hash.";
}
?>