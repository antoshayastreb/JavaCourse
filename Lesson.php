<?php
session_start();
if (array_key_exists('user_id',$_SESSION)) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: Enter.php");
    }
}elseif (array_key_exists('teach_id',$_SESSION)){
    if (!isset($_SESSION['teach_id'])) {
        header("Location: TeacherEnter.php");
    }
}else{
        header("Location: Enter.php");
    }
if ( array_key_exists('stage',$_SESSION)){
    $ThisStage = $_SESSION['stage'];
    if ($ThisStage) {
        require('connect.php');
        $stmt = $db->prepare("select `body` from `jc_lessons` where `Stage`=?");
        $stmt->execute(array($ThisStage));
        $stmt->bindColumn(1, $lob, PDO::PARAM_LOB);
        $stmt->fetch(PDO::FETCH_BOUND);
       if (strlen($lob) > 1000) {
           header("Content-type: application/pdf");
       }
        echo $lob;
    }
}
?>

