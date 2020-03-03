<?php
    //$connection = mysqli_connect('localhost', 'root');
    //$select_db = mysqli_select_db($connection, 'javacourses');
try {
    $db = new PDO('mysql:dbname=javacourses;host=localhost',
        'root','');
} catch (PDOException $e) {
    print "Couldn't connect to the database: " . $e->getMessage();
}
?>