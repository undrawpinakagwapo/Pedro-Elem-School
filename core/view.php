<?php 
// views.php
function loadView($viewPath, $data = []) {
    // extract($data); // Extracts the array into variables
    // include __DIR__ . "/$viewPath.php"; // Include the view file

    // Start output buffering
    ob_start();
    
    // Extract variables from the data array to use in the view
    extract($data);

    // Include the view file
    include __DIR__ . '/' . $viewPath . '.php';

    // Get the buffered content and clean the buffer
    return ob_get_clean();
}
?>