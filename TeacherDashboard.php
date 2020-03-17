<?php
    session_start();
    if (!isset($_SESSION['teach_id'])) {
        header("Location: TeacherEnter.php");
    }
    $DeletingGroupID = 0;
    $ds = DIRECTORY_SEPARATOR;
    $LoadFile = false;
    $storeFolder = 'uploads'; // Указываем папку для загрузки
    $FileUploading = false;
    $TDMode = 0;
    $currentGroup = 0;
    require('connect.php');
    $scMess = "";
    $flMess = "";
    $ID = $_SESSION['teach_id'];
    $sql = "SELECT * FROM jc_teachers WHERE `ID`=:ID ";
    $sth = $db->prepare($sql);
    $sth->bindValue(':ID', $ID);
    try {
        $sth->execute();
        $row = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (count($row) > 0) {
            $scMess ="Добро пожаловать, ".$row[0]['LastName']." ".$row[0]['FirstName']." ".$row[0]['patronymic'];
        }
    }
    catch (PDOException $e)
    {
        $flMess = 'Ошибка Базы Данных!';
    }
    if (count($_GET)>0) {
        //команды
        if ( array_key_exists('do',$_GET)){
            if($_GET['do'] == 'TDEDshow'){
                $TDMode = 2;
            }
            if($_GET['do'] == 'TDEDchange'){
                $TDMode = 2;
                if ( array_key_exists('stage',$_GET)){
                    $NewStage = $_GET['stage'];
                    $_SESSION['stage'] = $NewStage;
                    $FileUploading = true;
                }

            }
            //добавление нового урока
            if($_GET['do'] == 'TDEDadd'){
                $TDMode = 2;
                if ( array_key_exists('stage',$_GET)) {
                    $NewStage = $_GET['stage'];
                    $_SESSION['stage'] = $NewStage;
                    $FileUploading = true;
                    $DT = date("Y-m-d H:i:s");
                    try {
                        $sql="INSERT INTO jc_lessons (`ID`, `Stage`, `body`, `UpLoadDate`) VALUES (?,?,?,?)";
                        $sth = $db->prepare($sql);
                        $sth->execute(array(NULL, $NewStage, 'PDF - файл не загружен', $DT));
                        $insert_id = $db->lastInsertId();
                    } catch(PDOException $e){
                        $flMess = 'Ошибка Базы Данных!';
                    }

                }
            }

            //просмотр урока
            if($_GET['do'] == 'TDEDview'){
                $TDMode = 2;
                if ( array_key_exists('stage',$_GET)) {
                    $NewStage = $_GET['stage'];
                    $_SESSION['stage'] = $NewStage;
                    header("Location: Lesson.php");
                }
            }

            //удаление урока
            if($_GET['do'] == 'TDEDdelete'){
                $TDMode = 2;
                if ( array_key_exists('stage',$_GET)) {
                    $NewStage = $_GET['stage'];
                    try {
                        $sql="DELETE FROM `jc_lessons` WHERE `Stage`=?";
                        $sth = $db->prepare($sql);
                        $sth->execute(array($NewStage));
                    } catch(PDOException $e){
                        $flMess = 'Ошибка Базы Данных!';
                    }

                }
            }
            //Продвинуть студента по урокам вперед
            if($_GET['do'] == 'TDSDAddStage'){
                if ( array_key_exists('user_id',$_GET)) {
                    $ThisID = $_GET['user_id'];
                    try {
                        $sth = $db->prepare("SELECT * FROM jc_students WHERE `ID`=?");
                        $sth->execute(array($ThisID));
                        $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                        if (count($array) == 1) {
                            $ThisStage = $array [0]['stage'];
                            $Chk=1;
                            $sth = $db->prepare("UPDATE jc_homeworks SET `checked` = ?  WHERE `user_id`=? AND `stage`=?");
                            $sth->execute(array($Chk, $ThisID, $ThisStage));
                            $ThisStage++;
                            $sth = $db->prepare("UPDATE jc_students SET `stage` = ?  WHERE `ID`=?");
                            $sth->execute(array($ThisStage, $ThisID));
                        }
                    } catch (PDOException $e) {
                        $flMess = 'Ошибка Базы Данных!';
                    }
                }
                if ( array_key_exists('InGroup',$_GET)) {
                    $currentGroup =  $_GET['InGroup'];
                    }
            }
            //скачать ДЗ
            if($_GET['do'] == 'TDSDDownload'){
                if ( array_key_exists('user_id',$_GET)) {
                    $ThisID = $_GET['user_id'];
                    if (array_key_exists('stage', $_GET)) {
                        $ThisStage = $_GET['stage'];
                        if (array_key_exists('InGroup', $_GET)) {
                            $currentGroup = $_GET['InGroup'];
                            // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
                            // если этого не сделать файл будет читаться в память полностью!
                            if (ob_get_level()) {
                                ob_end_clean();
                            }
                            $sth = $db->prepare("SELECT * FROM jc_students WHERE `ID`=?");
                            $sth->execute(array($ThisID));
                            $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                            $svFileName = "ДЗ.zip";
                            if (count($array)) {
                                $svFileName = $array[0]['LastName']."_".$array[0]['FirstName']."_Урок № ".$ThisStage.".zip";
                                }
                            $sth = $db->prepare("SELECT `body` FROM jc_homeworks  WHERE `user_id`=? AND `stage`=?");
                            $sth->execute(array($ThisID,$ThisStage));
                            $sth->bindColumn(1, $lob, PDO::PARAM_LOB);
                            $sth->fetch(PDO::FETCH_BOUND);
                            $FileSize = strlen($lob);
                            // заставляем браузер показать окно сохранения файла
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename=' . basename($svFileName));
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate');
                            header('Pragma: public');
                            header('Content-Length: '.$FileSize);
                            // читаем файл и отправляем его пользователю
                            echo $lob;
                        }
                    }
                }
            }
            if($_GET['do'] == 'TDEGview'){
                //редактор групп - просмотр
                $TDMode = 1;
            }
            if($_GET['do'] == 'TDEGdelete'){
                //редактор групп - удаление группы
                $TDMode = 1;
                if ( array_key_exists('id',$_GET)) {
                    $ThisID = $_GET['id'];
                    try {
                        $sql="DELETE FROM `jc_groups` WHERE `ID`=?";
                        $sth = $db->prepare($sql);
                        $sth->execute(array($ThisID));
                    } catch(PDOException $e){
                        $flMess = 'Ошибка Базы Данных!';
                    }
                    header("Location: TeacherDashboard.php?do=TDEGview");
                }
            }
            if($_GET['do'] == 'TDEPshow'){
                $TDMode = 3;
            }
        }

    }
    if (count($_POST)>0) {
        if ( array_key_exists('csGroup',$_POST)) {
            $currentGroup = $_POST['csGroup'];
        }
        if ( array_key_exists('newGroup',$_POST)) {
            $NewGroup = $_POST['newGroup'];
            try {
                $sql="INSERT INTO jc_groups (`ID`, `Name`) VALUES (?,?)";
                $sth = $db->prepare($sql);
                $sth->execute(array(NULL, $NewGroup));
                $insert_id = $db->lastInsertId();
            } catch(PDOException $e){
                $flMess = 'Ошибка Базы Данных!';
            }
            header("Location: TeacherDashboard.php?do=TDEGview");
        }
    }
        if (!empty($_FILES)) { // Проверяем пришли ли файлы от клиента
            //$FileUploading = true;
            $tempFile = $_FILES['file']['tmp_name']; //Получаем загруженные файлы из временного хранилища
            $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;
            $targetFile = $targetPath . $_FILES['file']['name'];
            move_uploaded_file($tempFile, $targetFile); // Перемещаем загруженные файлы из временного хранилища в нашу папку uploads
            if (!empty($_SESSION['stage'])) {
                if (array_key_exists('stage', $_SESSION)) {
                    $NewStage = $_SESSION['stage'];
                    try {
                        $DT = date("Y-m-d H:i:s");
                        $sql = "UPDATE `jc_lessons` SET `body` = ?, `UpLoadDate` = ?  WHERE `Stage`=?";
                        $stmt = $db->prepare($sql);
                        $fp = fopen($targetFile, 'rb');
                        $stmt->bindParam(1, $fp, PDO::PARAM_LOB);
                        $stmt->bindParam(2, $DT);
                        $stmt->bindParam(3, $NewStage);
                        $db->beginTransaction();
                        $stmt->execute();
                        $db->commit();
                        fclose($fp);
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
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> -->
    <?php
    if ($TDMode == 2){
        if ($FileUploading){
            print("<link href=\"css/dropzone.css\" type=\"text/css\" rel=\"stylesheet\" />\n");
            echo "<script src=\"js/dropzone.js\"></script>\n";
        }
            echo "<link href=\"css/grid.css\" rel=\"stylesheet\">\n";
    }
    ?>
    <title>Курсы Java. Режим преподавателя.</title>
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
        <button class="btn btn-outline-danger my-2 my-sm-0" onClick='location.href="TeacherEnter.php?do=logout"'>Выход</button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="TeacherDashboard.php">
                            <span data-feather="users"></span>
                            Студенты
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="TeacherDashboard.php?do=TDEGview">
                            <span data-feather="home"></span>
                            Учебные группы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="TeacherDashboard.php?do=TDEDshow">
                            <span data-feather="layers"></span>
                            Редактор уроков
                        </a>
                    </li>
                   <!-- <li class="nav-item">
                        <a class="nav-link" href="#">
                            <span data-feather="layers"></span>
                            Integrations
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="TeacherDashboard.php?do=TDEPshow">
                            <span data-feather="file"></span>
                            Профиль
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <?php if (strlen($flMess) > 0){
                    print("<h1 class=\"h2\">".$flMess."</h1>");
                }else{
                    echo "<h1 class=\"h2\">" . $scMess . "</h1>";
                }
                ?>
            </div>

            <?php
            if ($TDMode == 1) {
                //учебные группы
                echo "<form method=\"POST\" action=\"\">\n";
                echo "<h4>Редактор учебных групп:</h4>";
                echo "<label for=\"newGroup\">Добавить новую группу</label>";
                echo "    <input type=\"text\" class=\"form-control\" name=\"newGroup\" placeholder=\"\" value=\"\" required>";
                echo "    <button class=\"btn btn-primary btn-lg btn-block\" type=\"submit\">Добавить</button>";
                echo "</form>\n";
                $sth = $db->prepare("SELECT * FROM jc_groups ORDER BY `ID` DESC");
                $sth->execute();
                $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                $counter = 1;
                echo "<div class=\"table-responsive\">\n";
                echo "    <table class=\"table table-striped table-sm\">\n";
                echo "        <thead>\n";
                echo "        <tr>\n";
                echo "            <th>№ п./п.</th>\n";
                echo "            <th>Код группы</th>\n";
                echo "            <th>Действие</th>\n";
                echo "        </tr>\n";
                echo "        </thead>\n";
                echo "        <tbody>\n";
                if (count($array)) {
                    foreach ($array as $key => $value) {
                        echo "        <tr>\n";
                        echo "            <td>" . $counter . "</td>\n";
                        echo "            <td>" . $value['Name'] . "</td>\n";
                        //echo "            <td><a href=\"TeacherDashboard.php?do=TDEGdelete&id=".$value['ID']."\">Удалить </a></td>\n";
                        echo  "            <td><button type=\"button\" class=\"btn btn-primary\" data-toggle=\"modal\" data-target=\"#staticBackdrop\" data-content=\"Содержимое 1...\">Удалить</button></td>\n";
                        echo "        </tr>\n";
                        $counter++;
                    }
                }
            } elseif ($TDMode == 2) {
                //редактор уроков
                if ($FileUploading) {
                    if ($NewStage > 0) {
                        print ("<h1>Загрузка файла в формате *.PDF для урока №" . $NewStage . "</h1>\n");
                        print ("<h2>Загружать не более одного файла</h2>\n");
                        print ("<h3>По окончании загрузки, нажмите ссылку \"Назад\"</h3>\n");
                        Print("<form action=\"TeacherDashboard.php\" class=\"dropzone\" id=\"pdfdropzone\"></form>");
                        print("<link href=\"css/dropzone.css\" type=\"text/css\" rel=\"stylesheet\" />
                        <script>
                            Dropzone.options.pdfdropzone = {
                                maxFiles: 1, //за раз грузить  только один файл
                                accept: function(file, done) {
                                    //произвольная функция проверки загружаемых файлов
                                    if (file.type == \"application/pdf\") {
                                        //чтобы файл был принят, нужно вызвать done без параметров
                                        done();
                                    }
                                    //файл не тот!
                                    else { done(\"только PDF!\"); }
                                }
                            };
                        </script>\n");
                    }else{
                        print ("<h1>Произошла ошибка создания/изменения урока. Нажмите ссылку \"Назад\"</h1>\n");
                    }
                    print("<a href=\"TeacherDashboard.php?do=TDEDshow\">Назад</a>");
                }else{
                    print ("<div class=\"container\">\n");
                    print ("<h1>Уроки в базе данных</h1>\n");
                    print("<div class=\"row\">\n");
                    print("    <div class=\"col-4\"><b>Порядковый номер урока в курсе</b></div>\n");
                    print("    <div class=\"col-4\"><b>Дата и время загрузки</b></div>\n");
                    print("    <div class=\"col-4\"><b>Действие</b></div>\n");
                    print("</div>\n");
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
                    //загрузка таблицы
                    if ($MaxStageNum > 0) {
                        $sql = "SELECT `Stage`, `UpLoadDate` FROM jc_lessons ORDER BY `Stage`";
                        $sth = $db->prepare($sql);
                        try {
                            $sth->execute();
                            $idx = 1;
                            $row = $sth->fetchAll();
                            if (count($row) > 0) {
                                for ($i = 0; $i < count($row);$i++) {
                                    while ($idx != $row[$i][0]) {
                                        print("<div class=\"row mb-3\">\n");
                                        print("    <div class=\"col-md-8 themed-grid-col\">Новый урок №" . $idx . "</div>\n");
                                        print("    <div class=\"col-md-4 themed-grid-col\"><a href=\"TeacherDashboard.php?do=TDEDadd&stage=" . $idx . "\">Добавить</a></div>\n");
                                        print("</div>\n");
                                        $idx++;
                                    }
                                    if ($idx == $row[$i][0]) {
                                        print("<div class=\"row\">\n");
                                        print("    <div class=\"col-4\">Урок № " . $row[$i][0] . "</div>\n");
                                        print("    <div class=\"col-4\">" . $row[$i][1] . "</div>\n");
                                        print("    <div class=\"col-4\"><a href=\"TeacherDashboard.php?do=TDEDchange&stage=" . $row[$i][0] . "\">Изменить</a>" . " <a href=\"TeacherDashboard.php?do=TDEDdelete&stage=" . $row[$i][0] . "\">Удалить</a>" . " <a href=\"TeacherDashboard.php?do=TDEDview&stage=" . $row[$i][0] . "\">Просмотреть</a></div>\n");
                                        print("</div>\n");
                                    }
                                    $idx++;
                                }
                            }
                        } catch (PDOException $e) {
                            $flMess = 'Ошибка Базы Данных!';
                        }
                    }
                    //футер таблицы
                    print("<div class=\"row mb-3\">\n");
                    print("    <div class=\"col-md-8 themed-grid-col\">Новый урок №".($MaxStageNum + 1)."</div>\n");
                    print("    <div class=\"col-md-4 themed-grid-col\"><a href=\"TeacherDashboard.php?do=TDEDadd&stage=".($MaxStageNum + 1)."\">Добавить</a></div>\n");
                    print("</div>\n");
                    print("</div>\n");
                }

            } elseif($TDMode == 3) {
                //require ("ModalWin.php");
            }else{
                //студенты
                echo "<form method=\"POST\" action=\"\">\n";
                echo "<h4>Ваши студенты из группы:</h4>";
                echo "<select class=\"custom-select d-block w-100\" name=\"csGroup\" required>\n";
                $sth = $db->prepare("SELECT * FROM jc_groups ORDER BY `ID` DESC");
                $sth->execute();
                $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                if ($currentGroup == 0) {
                    $currentGroup = $array[0]['ID'];
                    foreach ($array as $key => $value) {
                        print "  <option value=\"$value[ID]\">$value[Name]</option>\n";
                    }
                }else{
                    foreach ($array as $key => $value) {
                        if ($value['ID'] == $currentGroup){
                            echo "   <option value=\"$value[ID]\">$value[Name]</option>\n";
                            break;
                        }
                    }
                    foreach ($array as $key => $value) {
                        if ($value['ID'] != $currentGroup){
                            print "  <option value=\"$value[ID]\">$value[Name]</option>\n";
                        }
                    }
                }

                echo "</select>\n";
                echo "<button class=\"btn btn-primary btn-lg btn-block\" type=\"submit\">Показать</button>";
                echo "</form>\n";
                $sth = $db->prepare("SELECT * FROM jc_students WHERE `InGroup`=?");
                $sth->execute(array($currentGroup));
                $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                $counter = 1;
                echo "<div class=\"table-responsive\">\n";
                echo "    <table class=\"table table-striped table-sm\">\n";
                echo "        <thead>\n";
                echo "        <tr>\n";
                echo "            <th>№ п./п.</th>\n";
                echo "            <th>Фамилия</th>\n";
                echo "            <th>Имя</th>\n";
                echo "            <th>Отчество</th>\n";
                echo "            <th>Номер урока</th>\n";
                echo "            <th>Домашнее задание (архив)</th>\n";
                echo "            <th>Проверить</th>\n";
                echo "        </tr>\n";
                echo "        </thead>\n";
                echo "        <tbody>\n";
                if (count($array)) {
                    foreach ($array as $key => $value) {
                        $tmpSql = $db->prepare("SELECT * FROM jc_homeworks WHERE `user_id`=? AND `stage`=? ORDER BY `uploaded` DESC");
                        $tmpSql->execute(array($value['ID'], $value['stage']));
                        $tmpArr = $tmpSql->fetchAll(PDO::FETCH_ASSOC);
                        echo "        <tr>\n";
                        echo "            <td>" . $counter . "</td>\n";
                        echo "            <td>" . $value['LastName'] . "</td>\n";
                        echo "            <td>" . $value['FirstName'] . "</td>\n";
                        echo "            <td>" . $value['patronymic'] . "</td>\n";
                        echo "            <td>" . $value['stage'] . "</td>\n";
                        if (count($tmpArr) == 0) {
                            echo "            <td>Не загружено </td>\n";
                            echo "            <td> Проверка недоступна</td>\n";
                        }else{
                            echo "            <td><a href=\"TeacherDashboard.php?do=TDSDDownload&user_id=" . $value['ID'] . "&stage=".$value['stage']. "&InGroup=".$value['InGroup']."\">Скачать</a> (Загружено: ".$tmpArr[0]['uploaded'].")</td>\n";
                            if ($tmpArr[0]['checked'] == "1") {
                                echo "            <td>Проверено</td>\n";
                            }else{
                                echo "            <td><a href=\"TeacherDashboard.php?do=TDSDAddStage&user_id=" . $value['ID'] . "&InGroup=".$value['InGroup']."\">Установить статус \"Проверено\"</a></td>\n";
                            }
                        }
                        echo "        </tr>\n";
                        $counter++;
                    }
                }
                echo "    </table>";
                echo"</div>\n";
            }
            ?>
            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Запрос на удаление группы</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p id="content"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Отказаться</button>
                            <button type="button" class="btn btn-primary">Удалить</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // при открытии модального окна
                $('#staticBackdrop').on('show.bs.modal', function (event) {
                    // получить кнопку, которая его открыло
                    var button = $(event.relatedTarget)
                    // извлечь информацию из атрибута data-content
                    var content = button.data('content')
                    // вывести эту информацию в элемент, имеющий id="content"
                    $(this).find('#content').text(content);
                })
            </script>
        </main>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
        <script>
            feather.replace()
        </script>

</body>
</html>
