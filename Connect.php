<?php

try {
    $db = new PDO('mysql:dbname=javacourses;host=localhost',
        'root','');
} catch (PDOException $e) {
    print "Couldn't connect to the database: " . $e->getMessage();
}
?>