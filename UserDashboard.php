<?php
    session_start();
    $ds = DIRECTORY_SEPARATOR;
    $LoadFile = false;
    $storeFolder = 'uploads'; // Указываем папку для загрузки
    $UDMode = 0;
    if (!isset($_SESSION['user_id'])) {
        header("Location: Enter.php");
    }
    require('connect.php');
    $scMess = "";
    $flMess = "";
    $ThisStage = 0;
    $ID = $_SESSION['user_id'];
    $sql = "SELECT * FROM jc_students WHERE `ID`=:ID ";
    $sth = $db->prepare($sql);
    $sth->bindValue(':ID', $ID);
    try {
        $sth->execute();
        $row = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (count($row) > 0) {
           $scMess ="Здравствуйте, ".$row[0]['LastName']." ".$row[0]['FirstName']." ".$row[0]['patronymic'];
           $ThisStage = $row[0]['stage'];
           $_SESSION['stage'] = $ThisStage;
        }
    }
    catch (PDOException $e)
    {
        $flMess = 'Ошибка Базы Данных!';
    }
    //команды
    if (count($_GET)>0) {
        if (array_key_exists('do', $_GET)) {
            //удаление ДЗ
            if (($_GET['do'] == 'UDDelHM')|($_GET['do'] == 'UDChgHM')) {
                if (array_key_exists('stage', $_GET)) {
                    $NewStage = $_GET['stage'];
                    try {
                        $sql = "DELETE FROM `jc_homeworks` WHERE `user_id`=? AND `stage`=?";
                        $sth = $db->prepare($sql);
                        $sth->execute(array($ID, $NewStage));
                    } catch (PDOException $e) {
                        $flMess = 'Ошибка Базы Данных!';
                    }
                    header("Location: UserDashboard.php");
                }
            }
            //редактировать профиль
            if ($_GET['do'] == 'UDEditProfile'){
                $UDMode = 1;
            }
        }
    }
    if (!empty($_FILES)) { // Проверяем пришли ли файлы от клиента
        //$FileUploading = true;
        $tempFile = $_FILES['file']['tmp_name']; //Получаем загруженные файлы из временного хранилища
        $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;
        $targetFile = $targetPath . $_FILES['file']['name'];
        move_uploaded_file($tempFile, $targetFile); // Перемещаем загруженные файлы из временного хранилища в нашу папку uploads
        if (!empty($_SESSION['stage'])) {
            if ( array_key_exists('stage',$_SESSION)) {
                $NewStage = $_SESSION['stage'];
                try {
                    $DT = date("Y-m-d H:i:s");
                    $checked = 0;
                    $sql="INSERT INTO jc_homeworks (`user_id`, `stage`, `body`, `uploaded`, `checked`) VALUES (?,?,?,?,?)";
                    $stmt = $db->prepare($sql);
                    $fp = fopen($targetFile, 'rb');
                    $stmt->bindParam(1, $ID);
                    $stmt->bindParam(2, $NewStage);
                    $stmt->bindParam(3, $fp, PDO::PARAM_LOB);
                    $stmt->bindParam(4, $DT);
                    $stmt->bindParam(5, $checked);
                    $db->beginTransaction();
                    $stmt->execute();
                    $db->commit();
                    $LoadFile = true;
                } catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }
            }

        }
    }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../../../../favicon.ico">

    <title>Курсы Java</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
    <link href="css/dropzone.css" type="text/css" rel="stylesheet" />
    <script src="js/dropzone.js"></script>
</head>

<body>


<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
    <a class="navbar-brand" href="#">Top navbar</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" href="#">Disabled</a>
            </li>
        </ul>
        <button class="btn btn-outline-danger my-2 my-sm-0" onClick='location.href="Enter.php?do=logout"'>Выход</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="UserDashboard.php">
                            <span data-feather="home"></span>
                            Ваш Текущий урок<span class="sr-only">(current)</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="UserDashboard.php?do=UDEditProfile">
                            <span data-feather="users"></span>
                            Ваш профиль
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
            <?php
            if($UDMode) {
                //редактор профиля
            }else{
                echo "<div class=\"d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom\">\n";
                echo "<h1 class=\"h2\">".$scMess."</h1>\n";
                echo "    <div class=\"btn-toolbar mb-2 mb-md-0\">\n";
                echo "        <div class=\"btn-group mr-2\">\n";
                echo "            <button class=\"btn btn-sm btn-outline-secondary\">Предыдущий</button>\n";
                echo "            <button class=\"btn btn-sm btn-outline-secondary\">Следующий</button>\n";
                echo "        </div>\n";
                echo "    </div>\n";
                echo "</div>\n";
                echo "<object align=\"absmiddle\"><embed src=\"Lesson.php\" width=1280  height=\"700\"/></object>\n";
                $sql = "SELECT * FROM jc_homeworks WHERE `user_id`=:ID AND `stage`=:stage";
                $sth = $db->prepare($sql);
                $sth->bindValue(':ID', $ID);
                $sth->bindValue(':stage', $ThisStage);
                try {
                    $sth->execute();
                    $row = $sth->fetchAll(PDO::FETCH_ASSOC);
                    if (count($row) == 0) {
                        echo "<h3 class=\"h3\"> Для загрузки архива домашнего задания (в формете *zip или *.7z) используйте форму ниже. </h3>";
                        Print("<form action=\"UserDashboard.php\" class=\"dropzone\" id=\"zipdropzone\"></form>\n");
                        print("<script>
                    Dropzone.options.zipdropzone = {
                        maxFiles: 1, //за раз грузить  только один файл
                        acceptedFiles: \".zip,.7z\",
                    };
                </script>\n");
                    } else {
                        if (strlen($flMess) > 0) {
                            print("<div class=\"alert-danger\" role=\"alert\">" . $flMess . "</div>");
                        } else {
                            echo "<h3 class=\"h3\"> Домашнее задание к данному уроку было успешно загружено";
                            if ($row[0]['checked']) {
                                echo " и проверено преподавателем. Вам доступен следующий урок курса.</h3>";
                            } else {
                                echo ", но ещё не проверено преподавателем.</h3>\n";
                                echo "<h3 class=\"h3\">Вы можете <a href=\"UserDashboard.php?do=UDDelHM&stage=" . $ThisStage . "\">Удалить</a> или  <a href=\"UserDashboard.php?do=UDChgHM&stage=" . $ThisStage . "\">Изменить</a> его.</h3>";
                            }
                        }
                    }
                } catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }
            }
            ?>
        </main>
    </div>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="../../../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
<script src="../../../../assets/js/vendor/popper.min.js"></script>
<script src="../../../../dist/js/bootstrap.min.js"></script>

<!-- Icons -->
<script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
<script>
    feather.replace()
</script>


<?php
    require ('Disconnect.php');
?>
</body>
</html>
