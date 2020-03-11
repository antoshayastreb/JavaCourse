<?php
session_start();
$ds = DIRECTORY_SEPARATOR;
$storeFolder = 'uploads'; // Указываем папку для загрузки
$FileUploading = false;
$flMess = "";
$NewStage = 0;
require('connect.php');
if (count($_GET)>0) {
    if ( array_key_exists('do',$_GET)){
        if($_GET['do'] == 'change'){
            if ( array_key_exists('stage',$_GET)){
                $NewStage = $_GET['stage'];
                $_SESSION['stage'] = $NewStage;
                $FileUploading = true;
            }

        }

    //добавление нового урока
        if($_GET['do'] == 'add'){
            if ( array_key_exists('stage',$_GET)) {
                $NewStage = $_GET['stage'];
                $_SESSION['stage'] = $NewStage;
                $FileUploading = true;
                $DT = date("Y-m-d H:i:s");
                try {
                    $sql="INSERT INTO `jc_lessons` (`ID`, `Stage`, `body`, `UpLoadDate`) VALUES (?,?,?,?)";
                    $sth = $db->prepare($sql);
                    $sth->execute(array(NULL, $NewStage, 'PDF - файл не загружен', $DT));
                    $insert_id = $db->lastInsertId();
                } catch(PDOException $e){
                    $flMess = 'Ошибка Базы Данных!';
                }

            }
        }

    //просмотр урока
        if($_GET['do'] == 'view'){
            if ( array_key_exists('stage',$_GET)) {
                $NewStage = $_GET['stage'];
                $_SESSION['stage'] = $NewStage;
                header("Location: Lesson.php");
            }
        }

        //удаление урока
        if($_GET['do'] == 'delete'){
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
        if ( array_key_exists('stage',$_SESSION)) {
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
<!DOCTYPE html>
<html>
<head>
    <?php
    if ($FileUploading){
        print("<link href=\"css/dropzone.css\" type=\"text/css\" rel=\"stylesheet\" />
                <script src=\"js/dropzone.js\"></script>
                <script>
                    //настройка
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
        echo '<link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/grid.css" rel="stylesheet">
';
    }
    ?>
    <title>Курсы Java. Редактирование содержимого</title>
</head>
<body>
<?php
    if (strlen($flMess) > 0){
        print("<div class=\"alert-danger\" role=\"alert\">".$flMess."</div>");
    }
    if ($FileUploading) {
        if ($NewStage > 0) {
            print ("<h1>Загрузка файла в формате *.PDF для урока №" . $NewStage . "</h1>\n");
            print ("<h2>Загружать не более одного файла</h2>\n");
            print ("<h3>По окончании загрузки, нажмите ссылку \"Назад\"</h3>\n");
            Print("<form action=\"EditLessons.php\" class=\"dropzone\" id=\"pdfdropzone\"></form>");
        }else{
            print ("<h1>Произошла ошибка создания/изменения урока. Нажмите ссылку \"Назад\"</h1>\n");
        }
        print("<a href=\"EditLessons.php\">Назад</a>");
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
                           print("    <div class=\"col-md-4 themed-grid-col\"><a href=\"EditLessons.php?do=add&stage=" . $idx . "\">Добавить</a></div>\n");
                           print("</div>\n");
                           $idx++;
                       }
                       if ($idx == $row[$i][0]) {
                           print("<div class=\"row\">\n");
                           print("    <div class=\"col-4\">Урок № " . $row[$i][0] . "</div>\n");
                           print("    <div class=\"col-4\">" . $row[$i][1] . "</div>\n");
                           print("    <div class=\"col-4\"><a href=\"EditLessons.php?do=change&stage=" . $row[$i][0] . "\">Изменить</a>" . " <a href=\"EditLessons.php?do=delete&stage=" . $row[$i][0] . "\">Удалить</a>" . " <a href=\"EditLessons.php?do=view&stage=" . $row[$i][0] . "\">Просмотреть</a></div>\n");
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
        print("    <div class=\"col-md-4 themed-grid-col\"><a href=\"EditLessons.php?do=add&stage=".($MaxStageNum + 1)."\">Добавить</a></div>\n");
        print("</div>\n");
        print("</div>\n");
    }
?>
</body>
<?php
require ('Disconnect.php');
?>
</html>
