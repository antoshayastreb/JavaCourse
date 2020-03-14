<?php
    session_start();
    if (!isset($_SESSION['teach_id'])) {
        header("Location: TeacherEnter.php");
    }
    $ds = DIRECTORY_SEPARATOR;
    $LoadFile = false;
    $storeFolder = 'uploads'; // Указываем папку для загрузки
    $FileUploading = false;
    $TDMode = 0;
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
                    </li> --!>
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

            } else {
                //студенты
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
