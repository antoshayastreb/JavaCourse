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
    $email = "";
    $ingroup = "";
    $teacher = "";
    $lastName = "";
    $fistName = "";
    $patronymic = "";
    $oldpasshash = "";
    $newpass= "";
    $ThisStage = 0;
    $MaxStage = 0;
    $ID = $_SESSION['user_id'];
    $sql = "SELECT * FROM jc_students WHERE `ID`=:ID ";
    $sth = $db->prepare($sql);
    $sth->bindValue(':ID', $ID);
    try {
        $sth->execute();
        $row = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (count($row) > 0) {
           $email = $row[0]['EMAIL'];
           $fistName = $row[0]['FirstName'];
           $lastName = $row[0]['LastName'];
           $patronymic = $row[0]['patronymic'];
           $oldpasshash = $row[0]['pass_hash'];
           $ingroup = $row[0]['InGroup'];
           $teacher = $row[0]['teacher'];
           $scMess ="Здравствуйте, ".$row[0]['LastName']." ".$row[0]['FirstName']." ".$row[0]['patronymic'].".";
           $ThisStage = $row[0]['stage'];
           $MaxStage = $ThisStage;
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
            //Предыдущий урок
            if (($_GET['do'] == 'UDPrevLesson')){
                if (array_key_exists('stage', $_GET)) {
                    $ThisStage = $_GET['stage'];
                    if ($ThisStage > 1){
                        $ThisStage--;
                        $_SESSION['stage'] = $ThisStage;
                    }
                }
            }
            //следующий урок
            if (($_GET['do'] == 'UDNextLesson')){
                if (array_key_exists('stage', $_GET)) {
                    $ThisStage = $_GET['stage'];
                    if ($ThisStage < $MaxStage){
                        $ThisStage++;
                        $_SESSION['stage'] = $ThisStage;
                    }
                }
            }
        }
    }
    if (count($_POST)>0) {
        if (isset($_POST['curpass']) && isset($_POST['newpass'])){
            $curpass = $_POST['curpass'];
            if (password_verify($curpass, $oldpasshash)){
                $newpass = $_POST['newpass'];
                $newpass = password_hash($newpass, PASSWORD_DEFAULT);
                try {
                    $sqlus = "UPDATE `jc_students` SET `EMAIL` = ?, `FirstName` = ?, `LastName` = ?, `patronymic` = ?
                    , `InGroup` = ?, `teacher` = ?, `pass_hash` = ? WHERE `ID`=?";
                    $stmt = $db->prepare($sqlus);
                    $stmt->bindParam(1, $_POST['email']);
                    $stmt->bindParam(2, $_POST['fistName']);
                    $stmt->bindParam(3, $_POST['lastName']);
                    $stmt->bindParam(4, $_POST['patronymic']);
                    $stmt->bindParam(5, $_POST['teacher']);
                    $stmt->bindParam(6, $_POST['InGroup']);
                    $stmt->bindParam(7, $newpass);
                    $stmt->bindParam(8, $ID);
                    $db->beginTransaction();
                    $stmt->execute();
                    $db->commit();
                    $scMess = "Данные сохранены!";
                } catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }
            }
            else{
                $flMess =  'Пароль неверный!';
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
                    fclose($fp);
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
            if (strlen($flMess) > 0) {
                print("<h1 class=\"h2\">" . $flMess . "</h1>");
            } else {
                echo "<h1 class=\"h2\">" . $scMess . "</h1>";
            }
            if($UDMode == 1) {
                //редактор профиля
                echo "<form method=\"POST\">";
                echo "<div class=\"form-row\">";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Имя</label>";
                echo "<input type=\"text\" class=\"form-control\" name=\"fistName\" value=\"$fistName\">";
                echo "</div>";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Фамилия</label>";
                echo "<input type=\"text\" class=\"form-control\" name=\"lastName\" value=\"$lastName\">";
                echo "</div>";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Отчество</label>";
                echo "<input type=\"text\" class=\"form-control\" name=\"patronymic\" value=\"$patronymic\">";
                echo "</div>";
                echo "</div>";
                echo "<div class=\"form-row\">";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Email</label>";
                echo "<input type=\"text\" class=\"form-control\" name=\"email\" value=\"$email\">";
                echo "</div>";
                echo "</div>";
                echo "<div class=\"form-row\">";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Преподаватель</label>";
                echo "<select class=\"custom-select d-block w-100\" name=\"teacher\">";
                $sqlteacher = "SELECT * FROM jc_teachers WHERE `ID`=:ID ";
                $sth = $db->prepare($sqlteacher);
                $sth->bindValue(':ID', $teacher);
                try {
                    $sth->execute();
                    $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($array as $key => $value) {
                        print "<option value=\"$value[ID]\">$value[FirstName] $value[patronymic] $value[LastName] </option>";
                    }
                    $sth = $db->prepare("SELECT * FROM jc_teachers");
                    $sth->execute();
                    $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($array as $key => $value) {
                        if ($value[ID]!= $teacher){
                            print "<option value=\"$value[ID]\">$value[FirstName] $value[patronymic] $value[LastName] </option>";
                        }
                    }
                }
                catch (PDOException $e)
                {
                    $flMess = 'Ошибка Базы Данных!';
                }
                echo "</select>";
                echo "</div>";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Группа</label>";
                echo "<select class=\"custom-select d-block w-100\" name=\"InGroup\">";
                $sqlgroup = "SELECT * FROM jc_groups WHERE `ID`=:ID ";
                $sth = $db->prepare($sqlgroup);
                $sth->bindValue(':ID', $ingroup);
                try {
                    $sth->execute();
                    $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($array as $key => $value) {
                        print "<option value=\"$value[ID]\">$value[Name]</option>";
                    }
                    $sth = $db->prepare("SELECT * FROM jc_groups");
                    $sth->execute();
                    $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($array as $key => $value) {
                        if ($value[ID]!= $ingroup){
                            print "<option value=\"$value[ID]\">$value[Name]</option>";
                        }
                    }
                }
                catch (PDOException $e)
                {
                    $flMess = 'Ошибка Базы Данных!';
                }
                echo "</select>";
                echo "</div>";
                echo "</div>";
                //Надо бы проверять хэши пароля и при несовпадении выдавать ошибку.
                echo "<div class=\"form-row\">";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Старый пароль</label>";
                echo "<input type=\"password\" class=\"form-control\" name=\"curpass\">";
                echo "</div>";
                echo "</div>";
                echo "<div class=\"form-row\">";
                echo "<div class=\"col-md-4 mb-3\">";
                echo "<label>Новый пароль</label>";
                echo "<input type=\"password\" class=\"form-control\" name=\"newpass\">";
                echo "</div>";
                echo "</div>";
                echo "<button class=\"btn btn-lg btn-primary btn-block\" type=\"submit\">Обновить</button>";
                echo "</form>";
            }else{
                echo "<div class=\"d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom\">\n";
                echo "<h1 class=\"h2\">".$scMess." Это урок № ".$ThisStage."</h1>\n";
                echo "    <div class=\"btn-toolbar mb-2 mb-md-0\">\n";
                echo "        <div class=\"btn-group mr-2\">\n";
                echo "            <button class=\"btn btn-sm btn-outline-secondary\" onClick='location.href=\"UserDashboard.php?do=UDPrevLesson&stage=" . $ThisStage . "\"'>Предыдущий</button>\n";
                echo "            <button class=\"btn btn-sm btn-outline-secondary\" onClick='location.href=\"UserDashboard.php?do=UDPNextLesson&stage=" . $ThisStage . "\"'>Следующий</button>\n";
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
                        echo "<h3 class=\"h3\"> Для загрузки архива домашнего задания (в формете *zip) используйте форму ниже. </h3>";
                        Print("<form action=\"UserDashboard.php\" class=\"dropzone\" id=\"zipdropzone\"></form>\n");
                        print("<script>
                    Dropzone.options.zipdropzone = {
                        maxFiles: 1, //за раз грузить  только один файл
                        acceptedFiles: \".zip\",
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
                                //echo "<h3 class=\"h3\">Вы можете <a href=\"UserDashboard.php?do=UDDelHM&stage=" . $ThisStage . "\">Удалить</a> или  <a href=\"UserDashboard.php?do=UDChgHM&stage=" . $ThisStage . "\">Изменить</a> его.</h3>";
                                echo "<h3 class=\"h3\">Вы можете <a  data-toggle=\"modal\" href=\"#staticBackdrop\">Удалить</a> или <a  data-toggle=\"modal\" href=\"#staticBackdrop\">Изменить</a> его.</h3>";
                            }
                        }
                    }
                } catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }
            }
            ?>
            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Запрос на удаление архива из БД</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p> Ваше домашнее задание будет навсегда удалено. Вы уверены? </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Отказаться</button>
                            <button type="button" class="btn btn-primary" onclick='location.href="UserDashboard.php?do=UDDelHM&stage=<?php echo $ThisStage ?>"'>Удалить</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
<script>
    feather.replace()
</script>
<?php
    require ('Disconnect.php');
?>
</body>
</html>
