<?php
?>
<!doctype html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Пример на bootstrap 4: Блог - двухколоночный макет блога с пользовательской навигацией, заголовком и содержанием. Версия v4.0.0">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Custom styles for this template -->
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:700,900" rel="stylesheet">
    <link href="Lesson.css" rel="stylesheet">
    <title>Курсы Java. Урок</title>
</head>

<body class="text-center">
<div class="nav-scroller py-1 mb-2">
    <nav class="nav d-flex justify-content-between">
        <a class="p-2 text-muted" href="Lesson.php">Во весь экран</a>
        <a class="p-2 text-muted" href="#">Загрузить выполненное задание</a>
        <a class="p-2 text-muted" href="#">Редактировать профиль</a>
        <a class="p-2 text-muted" href=Enter.php?do=logout">Выход</a>
    </nav>
</div>
<div class="cover-container d-flex h-100 p-3 mx-auto flex-column">
    <main role="main" class="inner cover">
        <object align="absmiddle"><embed src="Lesson.php" width=1280  height="700"/></object>
    </main>
</div>
<?php
require ('Disconnect.php');
?>
</body>