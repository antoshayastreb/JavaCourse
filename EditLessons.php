<?php
$ds = DIRECTORY_SEPARATOR;
$storeFolder = 'uploads'; // Указываем папку для загрузки
if (!empty($_FILES)) { // Проверяем пришли ли файлы от клиента
    $tempFile = $_FILES['file']['tmp_name']; //Получаем загруженные файлы из временного хранилища
    $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;
    $targetFile = $targetPath . $_FILES['file']['name'];
    move_uploaded_file($tempFile, $targetFile); // Перемещаем загруженные файлы из временного хранилища в нашу папку uploads
}
?>
<!DOCTYPE html>
<html>
<head>

    <link href="css/dropzone.css" type="text/css" rel="stylesheet" />
    <script src="js/dropzone.js"></script>
    <script>
        Dropzone.options.pdfdropzone = {
            maxFiles: 1,
            accept: function(file, done) {
                //произвольная функция проверки загружаемых файлов
                if (file.type == "application/pdf") {
                    //сообщение без ошибки, если файл забракован
                    done();
                }
                //чтобы файл был принят, нужно вызвать done без параметров
                else { done("только PDF!"); }
            }
        };
    </script>
</head>
<body>
<form action="EditLessons.php" class="dropzone" id="pdfdropzone"></form>
</body>
</html>
