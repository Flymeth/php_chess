<?php
// Copy pasted from:
// https://stackoverflow.com/questions/5193744/how-to-kill-a-all-php-sessions

// Finds all server sessions
session_start();
// Stores in Array
$_SESSION = array();
// Swipe via memory
if (ini_get("session.use_cookies")) {
    // Prepare and swipe cookies
    $params = session_get_cookie_params();
    // clear cookies and sessions
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Completely destroy our server sessions..
session_destroy();
// Just in case.. swipe these values too
ini_set('session.gc_max_lifetime', 0);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Done!</title>
</head>
<body>
    <h1>All sessions has been deleted!</h1>
    <a href="/">Nice!</a>
</body>
</html>