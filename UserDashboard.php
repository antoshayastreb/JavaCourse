<?php
    include ('Utils.php');
    session_start();
    $ds = DIRECTORY_SEPARATOR;
    $LoadFile = false;
    $storeFolder = 'uploads'; // Указываем папку для загрузки
    $UDMode = 0;
    if (!isset($_SESSION['user_id'])) {
        header("Location: Enter.php");
    }
    require('Connect.php');
    $first_enter = 0;
    $scMess = "";
    $flMess = "";
    $email = "";
    $ingroup = "";
    $teacher = "";
    $lastName = "";
    $fistName = "";
    $patronymic = "";
    $oldpasshash = "";
    $ThisStage = 0;
    $MaxAllowedStage = 0;
    $MaxStage = 0;
    $ThisTheme = "";
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
           if (isset ($_SESSION['first_enter'])){
               $first_enter = $_SESSION['first_enter'];
               $_SESSION['first_enter'] = 0;
           }
           if ($first_enter == 1){
               $scMess ="Здравствуйте, ".$row[0]['LastName']." ".$row[0]['FirstName']." ".$row[0]['patronymic'].". Ваша регистрация прошла успешно! ";
           }
           else {$scMess ="Здравствуйте, ".$row[0]['LastName']." ".$row[0]['FirstName']." ".$row[0]['patronymic'].".";}
           $ThisStage = $row[0]['stage'];
           $MaxAllowedStage = $row[0]['stage'];
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
            
            //Темы курса
            if ($_GET['do'] == 'UDShowFullList'){
                $UDMode = 2;
            }
            
            //Урок из списка
            if (($_GET['do'] == 'UDShowFromList')){
                if (array_key_exists('stage', $_GET)) {
                    $ThisStage = $_GET['stage'];
                    $_SESSION['stage'] = $ThisStage;
                }
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
            //скачать личные данные
            if (($_GET['do'] == 'UDDwnUserData')){
                $UDMode = 1;
                if (ob_get_level()) {
                    ob_end_clean();
                }
                $sth = $db->prepare("SELECT * FROM jc_students WHERE `ID`=?");
                $sth->execute(array($ID));
                $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                $FileSize = 0;
                $svFileName = "Личные_данные.txt";
                if (count($array)) {
                    $svFileName = $array[0]['LastName']."_".$array[0]['FirstName']."_".$svFileName;
                    $svFileName = rus2translit($svFileName);
                }
                $lob = "Фамилия: ".$array[0]['LastName']."
                Имя: ".$array[0]['FirstName']."
                Отчество: ".$array[0]['patronymic']."
                Email: ".$array[0]['EMAIL'];
                $FileSize = strlen($lob);
                // заставляем браузер показать окно сохранения файла
                header('Content-Description: File Transfer');
                header('Content-Type: 	text/plain');
                header('Content-Disposition: attachment; filename=' . basename($svFileName));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: '.$FileSize);
                // читаем файл и отправляем его пользователю
                echo $lob;
                exit;
            }
            //удалить личные данные
            if (($_GET['do'] == 'UDDelUserData')){
                $UDMode = 1;
                try {
                    //Удаляем все дз студента
                    $sql = "DELETE FROM `jc_homeworks` WHERE `user_id`=?";
                    $sth = $db->prepare($sql);
                    $sth->execute(array($ID));

                    //Удаляем студента
                    $sql="DELETE FROM jc_students WHERE `ID`=?";
                    $sql = $db->prepare($sql);
                    $sql->execute(array($ID));

                } catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }
                header("Location: index.php");

            }
        }
    }
    if (count($_POST)>0) {
        if (($_POST['curpass']!= "") and ($_POST['newpass']!= "")){
            $curpass = $_POST['curpass'];
            $newpass = $_POST['newpass'];
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
        else{
            try {
                $sqlus = "UPDATE `jc_students` SET `EMAIL` = ?, `FirstName` = ?, `LastName` = ?, `patronymic` = ?
                    , `InGroup` = ?, `teacher` = ? WHERE `ID`=?";
                $stmt = $db->prepare($sqlus);
                $stmt->bindParam(1, $_POST['email']);
                $stmt->bindParam(2, $_POST['fistName']);
                $stmt->bindParam(3, $_POST['lastName']);
                $stmt->bindParam(4, $_POST['patronymic']);
                $stmt->bindParam(5, $_POST['teacher']);
                $stmt->bindParam(6, $_POST['InGroup']);
                $stmt->bindParam(7, $ID);
                $db->beginTransaction();
                $stmt->execute();
                $db->commit();
                $scMess = "Данные сохранены!";
            } catch (PDOException $e) {
                $flMess = 'Ошибка Базы Данных!';
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

    <title>Курсы Java</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/dropzone.css" type="text/css" rel="stylesheet" />
    <script src="js/dropzone.js"></script>
</head>

<body>

<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
    <a class="navbar-brand" href="#">Java курс</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="UserDashboard.php">Уроки</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="UserDashboard.php?do=UDEditProfile">Профиль</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="UserDashboard.php?do=UDShowFullList">Темы курса</a>
            </li>
        </ul>
        <button class="btn btn-outline-danger my-2 my-sm-0" onClick='location.href="index.php?do=logout"'>Выход</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
       

        <div role="main" class="col">
            <?php
            if (strlen($flMess) > 0) {
                print("<h1 class=\"h2\">" . $flMess . "</h1>");
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
                echo "<button class=\"btn btn-primary btn-lg\" type=\"submit\">Обновить</button>\n";
                echo "</form>";
                echo "<br>\n";
                echo "<h4>Дополнительные действия</h4>\n";
                echo "<br>\n";
                echo "<button type=\"button\" class=\"btn btn-secondary\" onclick='location.href=\"UserDashboard.php?do=UDDwnUserData\"'>Скачать личные данные</button>\n";
                echo "<a class=\"btn btn-warning\" role=\"button\" data-toggle=\"modal\" href=\"#staticBackdropDelData\">Удалить аккаунт</a>\n";
                echo "<footer class=\"mastfoot mt-auto\">\n";
                echo "<div class=\"inner\">\n";
                echo "<p>\n";
                echo "</div>\n";
                echo "</footer>\n";
            //просмотр всего курса    
            }elseif ($UDMode == 2) {
                //echo "<form method=\"POST\" action=\"\">\n";
                echo "<h4>Все уроки в курсе:</h4>";
                $sth = $db->prepare("SELECT * FROM jc_lessons");
                $sth->execute(array());
                $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class=\"table-responsive\">\n";
                echo "    <table class=\"table table-striped table-sm\">\n";
                echo "        <thead>\n";
                echo "        <tr>\n";
                echo "            <th>№ зад.</th>\n";
                echo "            <th>Тема</th>\n";
                echo "            <th>Решено</th>\n";
                echo "            <th>Действие</th>\n";
                echo "        </tr>\n";
                echo "        </thead>\n";
                echo "        <tbody>\n";
                $MaxStageNum = 0;
                //нахождение максимального номера урока
                $sql = "SELECT MAX(`Stage`) FROM jc_lessons";
                $sth = $db->prepare($sql);
                try{
                    $sth->execute();
                    $MaxStageNum = $sth->fetchColumn();
                }catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }
                $sql = "SELECT `Stage`, `theme` FROM jc_lessons ORDER BY `Stage`";
                $sth = $db->prepare($sql);
                        try {
                            $sth->execute();
                            $idx = 1;
                            $row = $sth->fetchAll();
                            if (count($row) > 0) {
                                for ($i = 0; $i < count($row);$i++) {
                                    $NewStage = $row[$i][0];
                                    $sqlh = "SELECT `checked` FROM jc_homeworks WHERE `user_id`=? AND `stage`=?";
                                    $sthh = $db->prepare($sqlh);
                                    //$sthh->bindParam(1, $ID);
                                    //$sthh->bindParam(2, $NewStage);
                                    try{
                                        $sthh->execute(array($ID, $NewStage));
                                        $rowh = $sthh->fetchAll();
                                    }catch(PDOException $e){
                                        $flMess = 'Ошибка Базы Данных!';
                                    }
                                    try{
                                        $sth->execute();
                                    }catch (PDOException $e) {
                                        $flMess = 'Ошибка Базы Данных!';
                                    }
                                    if (($idx == $row[$i][0]) and ($MaxAllowedStage >= $row[$i][0])) {
                                        echo "        <tr>\n";
                                        echo "            <td>" . $row[$i][0] . "</td>\n";
                                        echo "            <td>" . $row[$i][1] . "</td>\n";
                                        if (!$rowh == null){
                                            if ($rowh[0]['checked']==1){
                                            echo "            <td> Да </td>\n";
                                        }else{
                                            echo "            <td> Нет </td>\n";
                                        }
                                        }else {
                                            echo "            <td> Нет дз</td>\n";
                                        }
                                        echo "            <td> <button type=\"button\" class=\"btn btn-secondary\" onclick='location.href=\"UserDashboard.php?do=UDShowFromList&stage=" . $row[$i][0] . "\"'>Просмотреть</button></td>\n";
                                        echo "        </tr>\n";
                                    }else{
                                        echo "        <tr>\n";
                                        echo "            <td>" . $row[$i][0] . "</td>\n";
                                        echo "            <td>" . $row[$i][1] . "</td>\n";
                                        if (!$rowh == null){
                                            if ($rowh[0]['checked']==1){
                                            echo "            <td> Да </td>\n";
                                        }else{
                                            echo "            <td> Нет </td>\n";
                                        }
                                        }else {
                                            echo "            <td> Нет дз</td>\n";
                                        }
                                        echo "            <td> </td>\n";
                                        echo "        </tr>\n";
                                    }
                                    $idx++;
                                }
                            }    
                        }catch (PDOException $e) {
                            $flMess = 'Ошибка Базы Данных!';
                        }             
            }else{
                try {
                    $sth = $db->prepare("SELECT * FROM `jc_lessons` WHERE `Stage`=?");
                    $sth->execute(array($ThisStage));
                    $row = $sth->fetchAll(PDO::FETCH_ASSOC);
                    if (count($row) == 1) {
                        $ThisTheme = $row [0]['theme'];   
                    }
                } catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }    
                echo "<div class=\"d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom\">\n";
                echo "<h1 class=\"h2\">".$scMess." Это урок № ".$ThisStage." ".$ThisTheme."</h1>\n";
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
                                echo "<h3 class=\"h3\">Вы можете <a  data-toggle=\"modal\" href=\"#staticBackdropDelHW\">Удалить</a> или <a  data-toggle=\"modal\" href=\"#staticBackdropDelHW\">Изменить</a> его.</h3>";
                            }
                        }
                    }
                } catch (PDOException $e) {
                    $flMess = 'Ошибка Базы Данных!';
                }
            }
            ?>
            <!-- Modal
            модал удаление дз -->
            <div class="modal fade" id="staticBackdropDelHW" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
            <!-- модал удаление данных -->
            </div>
            <div class="modal fade" id="staticBackdropDelData" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Запрос на удаление архива из БД</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p> Ваше данные, а также домашнее задание будут навсегда удалено. Вы уверены? </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Отказаться</button>
                            <button type="button" class="btn btn-primary" onclick='location.href="UserDashboard.php?do=UDDelUserData&stage=<?php echo $ThisStage ?>"'>Удалить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

    function slugify($string) {
        $translit = "Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();";
        $string = transliterator_transliterate($translit, $string);
        $string = preg_replace('/[-\s]+/', '-', $string);
        return trim($string, '-');
    }
</script>
<?php
    require ('Disconnect.php');
?>
<script type="text/javascript" id="cookieinfo"
        src="//cookieinfoscript.com/js/cookieinfo.min.js"
        data-message="Этот сайт использует cookie файлы для хранения информации. Продолжая пользоваться сайтом, вы автоматически соглашаетесь с обработкой файлов cookie."
        data-cookie="CookieInfoScript"
        data-close-text="Понятно"
        data-fg="#FFF"
        data-bg="#333"
        data-divlink="#FFFFFF"
        data-divlinkbg="#007BFF"
        data-linkmsg="Больше информации"
        data-moreinfo="https://ru.wikipedia.org/wiki/Cookie">
</script>
</body>
</html>
