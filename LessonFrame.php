<?php
?>
<!doctype html>

<head>
    <meta charset="utf-8">
    <meta name="author" content="">
    <title>Курсы Java. Урок</title>
</head>

<br>
<hr align="middle"/>Это страница урока</hr>
</br>
<object align="absmiddle"><embed src="./Lessons/Lesson1.pdf" width=<?php echo $pageWidth ?>  height="1000"/></object>
</br>
<a href="Enter.php?do=logout" align="middle">Выход</a>
<?php
require ('Disconnect.php');
?>
</body>