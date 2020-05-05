<?php
    session_start();
    include ('Utils.php');
    if (!isset($_SESSION['teach_id'])) {
        header("Location: TeacherEnter.php");
    }
    $NewGroup = "";
    $DeletingGroupID = 0;
    $ds = DIRECTORY_SEPARATOR;
    $LoadFile = false;
    $storeFolder = 'uploads'; // Указываем папку для загрузки
    $FileUploading = false;
    $TDMode = 0;
    $NewTheme = "";
    $CurTheme = "";
    $currentGroup = 0;
    require('Connect.php');
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
            $scMess =" ";
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
            //запрос на изменение темы урока    
            if($_GET['do'] == 'TDEDchangeThemeConf'){
                $TDMode = 22;
                if ( array_key_exists('stage',$_GET)) {
                    $NewStage = $_GET['stage'];
                    $sth = $db->prepare("SELECT * FROM `jc_lessons` WHERE `Stage`=?");
                    $sth->execute(array($NewStage));
                    $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                    if (count($array) == 1){
                        $CurTheme = $array [0]['theme'];
                    }
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
            //запрос на удаление урока
            if($_GET['do'] == 'TDEDdelConf'){
                $TDMode = 21;
                if ( array_key_exists('stage',$_GET)) {
                    $NewStage = $_GET['stage'];
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
                                $svFileName = rus2translit($svFileName);
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
                            exit;
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
                        //Достаем всех студентов в удаляемой группе
                        $sth = $db->prepare("SELECT * FROM jc_students WHERE `InGroup`=?");
                        $sth->execute(array($ThisID));
                        $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                        if (count($array)) {
                            foreach ($array as $key => $value) {
                                //удаляем все ДЗ
                                $tmpSql = $db->prepare("DELETE FROM jc_homeworks WHERE `user_id`=?");
                                $tmpSql->execute(array($value['ID']));
                            }
                        }
                        //удаляем всех студентов
                        $sql="DELETE FROM jc_students WHERE `InGroup`=?";
                        $sql = $db->prepare($sql);
                        $sql->execute(array($ThisID));
                        //удаляем группу
                        $sql="DELETE FROM `jc_groups` WHERE `ID`=?";
                        $sth = $db->prepare($sql);
                        $sth->execute(array($ThisID));
                    } catch(PDOException $e){
                        $flMess = 'Ошибка Базы Данных!';
                    }
                    header("Location: TeacherDashboard.php?do=TDEGview");
                }
            }
            if($_GET['do'] == 'TDEGdelConf'){
                //редактор групп - запрос на удаление группы
                $TDMode = 11;
                if ( array_key_exists('id',$_GET)) {
                    $ThisID = $_GET['id'];
                    try {
                        $sql="SELECT * FROM jc_groups WHERE `ID`=?";
                        $sth = $db->prepare($sql);
                        $sth->execute(array($ThisID));
                        $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                        if (count($array)){
                            $NewGroup = $array[0]['Name'];
                        }
                    } catch(PDOException $e){
                        $flMess = 'Ошибка Базы Данных!';
                    }
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
        if (array_key_exists('theme-name',$_POST)){
            $NewTheme = $_POST['theme-name'];
            $TDMode = 2;
                if ( array_key_exists('stage',$_GET)) {
                    $NewStage = $_GET['stage'];
                    try {
                        $sql="UPDATE `jc_lessons` SET `theme` = ? WHERE `Stage` = ?";
                        $sth = $db->prepare($sql);
                        $sth->bindParam(1,$NewTheme);
                        $sth->bindParam(2,$NewStage);
                        $db->beginTransaction();
                        $sth->execute();
                        $db->commit();
                    } catch(PDOException $e){
                        $flMess = 'Ошибка Базы Данных!';
                    }
                    header("Location: TeacherDashboard.php?do=TDEDshow");        
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
    }
    ?>
    <title>Курсы Java. Режим преподавателя.</title>
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
                <a class="nav-link" href="TeacherDashboard.php">Студенты</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="TeacherDashboard.php?do=TDEGview">Учебные группы</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="TeacherDashboard.php?do=TDEDshow">Редактор курса</a>
            </li>
        </ul>
        <span class="navbar-text">
            <?php echo $row[0]['LastName'], ' ', $row[0]['FirstName'], ' ', $row[0]['patronymic']?> <a href="index.php?do=logout">Выход</a>
        </span>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        
        <main role="main" class="col">
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
                        echo  "            <td><button type=\"button\" class=\"btn btn-primary\" onclick='location.href=\"TeacherDashboard.php?do=TDEGdelConf&id=".$value['ID']."\"'>Удалить</button></td>\n";
                        echo "        </tr>\n";
                        $counter++;
                    }
                }
            }elseif ($TDMode == 11){
            echo"<div class=\"modal fade\" id=\"staticBackdrop\" data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">\n";
            echo "   <div class=\"modal-dialog\" role=\"document\">\n";
            echo"        <div class=\"modal-content\">\n";
            echo "           <div class=\"modal-header\">\n";
            echo"                <h5 class=\"modal-title\" id=\"staticBackdropLabel\">Запрос на удаление группы</h5>\n";
            echo"            </div>\n";
            echo"            <div class=\"modal-body\">\n";
            echo"                <p>При удалении группы ".$NewGroup.", будут удалены аккаунты всех студентов, входящих в нее, а также все загруженные ими домашние задания. Вы уверены?</p>\n";
            echo"            </div>\n";
            echo"            <div class=\"modal-footer\">\n";
            echo"                <button type=\"button\" class=\"btn btn-secondary\"  onclick='location.href=\"TeacherDashboard.php?do=TDEGview\"'>Отказаться</button>\n";
            echo"               <button type=\"button\" class=\"btn btn-primary\" onclick='location.href=\"TeacherDashboard.php?do=TDEGdelete&id=".$ThisID."\"'>Удалить</button>\n";
            echo"           </div>\n";
            echo"        </div>\n";
            echo"    </div>\n";
            echo"</div>\n";
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
                    echo "<h4>Уроки в курсе:</h4>";
                    echo "<div class=\"table-responsive\">\n";
                    echo "    <table class=\"table table-striped table-sm\">\n";
                    echo "        <thead>\n";
                    echo "        <tr>\n";
                    echo "            <th>Порядковый номер урока в курсе</th>\n";
                    echo "            <th>Тема урока</th>\n";
                    echo "            <th>Дата и время загрузки</th>\n";
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
                    //загрузка таблицы
                    if ($MaxStageNum > 0) {
                        $sql = "SELECT `Stage`, `theme`, `UpLoadDate` FROM jc_lessons ORDER BY `Stage`";
                        $sth = $db->prepare($sql);
                        try {
                            $sth->execute();
                            $idx = 1;
                            $row = $sth->fetchAll();
                            if (count($row) > 0) {
                                for ($i = 0; $i < count($row);$i++) {
                                    while ($idx != $row[$i][0]) {
                                        echo "        <tr>\n";
                                        echo "            <td>Новый урок №" . $idx . "</td>\n";
                                        echo "            <td></td>\n";
                                        echo "            <td></td>\n";
                                        echo "            <td><button type=\"button\" class=\"btn btn-primary\" onclick='location.href=\"TeacherDashboard.php?do=TDEDadd&stage=" . $idx . "\"'>Добавить</button></td>\n";
                                        echo "        </tr>\n";
                                        $idx++;
                                    }
                                    if ($idx == $row[$i][0]) {
                                        echo "        <tr>\n";
                                        echo "            <td> Урок № " . $row[$i][0] . "</td>\n";
                                        echo "            <td>" . $row[$i][1] . "</td>\n";
                                        echo "            <td>" . $row[$i][2] . "</td>\n";
                                        echo "            <td><button type=\"button\" class=\"btn btn-info\" onclick='location.href=\"TeacherDashboard.php?do=TDEDchangeThemeConf&stage=" . $row[$i][0] . "\"'>Изменить тему</button>" . " <button type=\"button\" class=\"btn btn-primary\" onclick='location.href=\"TeacherDashboard.php?do=TDEDchange&stage=" . $row[$i][0] . "\"'>Изменить урок</button>" . " <button type=\"button\" class=\"btn btn-warning\" onclick='location.href=\"TeacherDashboard.php?do=TDEDdelConf&stage=" . $row[$i][0] . "\"'>Удалить</button>" . " <button type=\"button\" class=\"btn btn-secondary\" onclick='location.href=\"TeacherDashboard.php?do=TDEDview&stage=" . $row[$i][0] . "\"'>Просмотреть</button></td>\n";
                                        echo "        </tr>\n";
                                    }
                                    $idx++;
                                }
                            }
                        } catch (PDOException $e) {
                            $flMess = 'Ошибка Базы Данных!';
                        }
                    }
                    //футер таблицы
                    echo "        <tr>\n";
                    echo "            <td>Новый урок №" . ($MaxStageNum + 1) . "</td>\n";
                    echo "            <td></td>\n";
                    echo "            <td></td>\n";
                    echo "            <td><button type=\"button\" class=\"btn btn-primary\" onclick='location.href=\"TeacherDashboard.php?do=TDEDadd&stage=" . ($MaxStageNum + 1) . "\"'>Добавить</button></td>\n";
                    echo "        </tr>\n";
                    echo "    </table>";
                    echo"</div>\n";
                }

            }elseif ($TDMode == 21){
                //запрос на удаление урока
                echo"<div class=\"modal fade\" id=\"staticBackdrop\" data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">\n";
                echo "   <div class=\"modal-dialog\" role=\"document\">\n";
                echo"        <div class=\"modal-content\">\n";
                echo "           <div class=\"modal-header\">\n";
                echo"                <h5 class=\"modal-title\" id=\"staticBackdropLabel\">Запрос на удаление урока</h5>\n";
                echo"            </div>\n";
                echo"            <div class=\"modal-body\">\n";
                echo"                <p>После нажатия кнопки \"Удалить\", урок №".$NewStage." будет удален навсегда. Вы уверены?</p>\n";
                echo"            </div>\n";
                echo"            <div class=\"modal-footer\">\n";
                echo"                <button type=\"button\" class=\"btn btn-secondary\"  onclick='location.href=\"TeacherDashboard.php?do=TDEDshow\"'>Отказаться</button>\n";
                echo"               <button type=\"button\" class=\"btn btn-primary\" onclick='location.href=\"TeacherDashboard.php?do=TDEDdelete&stage=" . $NewStage . "\"'>Удалить</button>\n";
                echo"           </div>\n";
                echo"        </div>\n";
                echo"    </div>\n";
                echo"</div>\n";
                
            }elseif ($TDMode == 22){
                //запрос на изменение темы урока
                echo"<div class=\"modal fade\" id=\"staticBackdrop\" data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">\n";
                echo "   <div class=\"modal-dialog\" role=\"document\">\n";
                echo"        <div class=\"modal-content\">\n";
                echo "           <div class=\"modal-header\">\n";
                echo"                <h5 class=\"modal-title\" id=\"staticBackdropLabel\">Изменить тему урока № ".$NewStage."</h5>\n";
                echo"            </div>\n";
                echo"            <div class=\"modal-body\">\n";
                echo"            <form method=\"post\">\n";
                echo"               <div class=\"form-row\">";
                //echo"                   <div class=\"col-md-4 mb-3\">";
                echo"                       <label>Тема урока: </label>";
                echo"                       <input type=\"text\" class=\"form-control\" name=\"theme-name\" value=\"$CurTheme\">";
                //echo"                   </div>";
                echo"              </div>";
                echo"              <br>\n";
                echo"              <button class=\"btn btn-primary btn-lg\" type=\"submit\">Изменить</button>\n";
                echo"              <button type=\"button\" class=\"btn btn-primary btn-lg\"  onclick='location.href=\"TeacherDashboard.php?do=TDEDshow\"'>Отказаться</button>\n";
                echo"           </form>\n";
                echo"            </div>\n";
                echo"        </div>\n";
                echo"    </div>\n";
                echo"</div>\n";
                
                
            }elseif($TDMode == 3) {
                //личный кабинет
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
                $sth = $db->prepare("SELECT * FROM jc_students WHERE `InGroup`=? AND `teacher`=?");
                $sth->execute(array($currentGroup, $ID));
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
        </main>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
        <script>
            feather.replace();
        </script>
        <?php
        require ('Disconnect.php');
        if (($TDMode == 11)| ($TDMode == 21) | ($TDMode == 22)){
            echo "<!-- Скрипт, вызывающий модальное окно после загрузки страницы -->\n";
            echo "<script>\n";
            echo "    $(document).ready(function() {\n";
            echo "       $(\"#staticBackdrop\").modal('show');\n";
            echo"    });\n";
            echo "</script>\n";
        }
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
    </div>
</body>
</html>
