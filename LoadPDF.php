<?php
require ('connect.php');
if (isset($_GET['LoadPDFName'])) {
    //$FileName=$_GET['LoadPDFName'];
    $FileName="C:\\xampp\\htdocs\\JavaCourse\\Lessons\\Lesson1.pdf";
    $id = null;
    $stage=1;
    $Homework=0;
    $stmt = $db->prepare("INSERT INTO `jc_lessons` (`ID`, `Stage`, `body`, `HomeWork`) VALUES (?,?,?,?)");
    $fp = fopen($FileName, 'rb');
    $stmt->bindParam(1, $id);
    $stmt->bindParam(2, $stage);
    $stmt->bindParam(3, $fp, PDO::PARAM_LOB);
    $stmt->bindParam(4, $Homework);
    $db->beginTransaction();
    $stmt->execute();
    $db->commit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="./css/signin.css" rel="stylesheet">
    <title>Курсы Java. Вход</title>
</head>
<body class="text-center">
<form>
    <div class="form-group">
        <label for=""LoadPDFName">Укажите файл *.pdf для загрузки в базу</label>
        <input type="file" class="form-control-file" name="LoadPDFName">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
<?php
require ('Disconnect.php');
?>
<input type="file" id="input" onchange="handleFiles(this.files)">

</body>
