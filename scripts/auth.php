<?php

if (!empty($_POST)) {
    require __DIR__.'/../liberes/Redmine.php'; 
    
    if ($_POST['login'] == Redmine::$login && $_POST['password'] == Redmine::$password) { 
        session_start();
        $_SESSION['user'] = random_int(1, 10000);
        
    }
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>