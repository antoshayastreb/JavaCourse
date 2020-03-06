<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Enter.php");
}
require ('connect.php');
$stmt = $db->prepare("select `body` from `jc_lessons` where `Stage`=?");
$stmt->execute(array(1));
$stmt->bindColumn(1, $lob, PDO::PARAM_LOB);
$stmt->fetch(PDO::FETCH_BOUND);
$pdf = base64_encode($lob);
header("Content-type: application/pdf");
//fpassthru($lob);
echo $lob;
?>
<!-- <object data="<?php echo $pdf ?>" type="application/pdf"></object>

