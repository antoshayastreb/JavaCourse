<?php
session_start();
$ds = DIRECTORY_SEPARATOR;
$storeFolder = 'uploads'; // Указываем папку для загрузки
$FileUploading = false;
if (count($_GET)>0) {
    if ( array_key_exists('do',$_GET)){
        if($_GET['do'] == 'change'){

        }

    }
    if ( array_key_exists('stage',$_GET)){
        $_SESSION['stage']=$_GET['stage'];
        $FileUploading = true;
    }
}

if (!empty($_FILES)) { // Проверяем пришли ли файлы от клиента
    //$FileUploading = true;
    $tempFile = $_FILES['file']['tmp_name']; //Получаем загруженные файлы из временного хранилища
    $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;
    $targetFile = $targetPath . $_FILES['file']['name'];
    move_uploaded_file($tempFile, $targetFile); // Перемещаем загруженные файлы из временного хранилища в нашу папку uploads
    exit;
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
                </script>");
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
    if ($FileUploading) {
        Print("<form action=\"EditLessons.php\" class=\"dropzone\" id=\"pdfdropzone\"></form>");
        print("<a href=\"EditLessons.php\">Назад</a>");
        }else{
        print ("<div class=\"container\">\n");
        print ("<h1>Уроки в базе данных</h1>\n");
        print("<div class=\"row\">\n");
        print("    <div class=\"col-4\"><b>Порядковый номер урока в курсе</b></div>\n");
        print("    <div class=\"col-4\"><b>Дата и время загрузки</b></div>\n");
        print("    <div class=\"col-4\"><b>Действие</b></div>\n");
        print("</div>\n");
        //загрузка таблицы
        require('connect.php');
        $sql = "SELECT `Stage`, `UpLoadDate` FROM jc_lessons ORDER BY `Stage`";
        $sth = $db->prepare($sql);
        $ExStages = [];
        $idx = 0;
        try {
            $sth->execute();
            while ($row = $sth->fetchAll()) {
                print("<div class=\"row\">\n");
                print("    <div class=\"col-4\">Урок № " . $row[0][0]."</div>\n");
                print("    <div class=\"col-4\">".$row[0][1]."</div>\n");
                print("    <div class=\"col-4\"><a href=\"EditLessons.php?do=change&stage=\"". $row[0][0] ."\">Изменить</a>"." <a href=\"EditLessons.php?do=delete&stage=\"". $row[0][0]."\">Удалить</a>"." <a href=\"EditLessons.php?do=delete&stage=\"". $row[0][0]."\">Просмотреть</a></div>\n");
                $ExStages [$idx] = $row[0][0];
                $idx++;
            }
        } catch (PDOException $e) {
            $flMess = 'Ошибка Базы Данных!';
        }
        print("</div>\n");
    }
?>
</body>
<?php
require ('Disconnect.php');
?>
</html>
