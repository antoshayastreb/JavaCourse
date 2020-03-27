<?php
session_start();
if ((count($_GET)>0) && array_key_exists('do',$_GET)){
if($_GET['do'] == 'logout'){
    session_destroy();
    unset($_SESSION['user_id']);
    header("Location: Enter.php");
}
}
$scMess = "";
$flMess = "";
require ('Connect.php');
if (isset($_POST['inputEmail']) and isset($_POST['inputPassword'])){
    $password = $_POST['inputPassword'];
    //$pass_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "SELECT * FROM jc_students WHERE `email`=:email ";
    $sth = $db->prepare($sql);
    $sth->bindValue(':email', $_POST['inputEmail']);
    try {
        $sth->execute();
        $row = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (count($row) > 0) {
            $pass_hash = $row[0]['pass_hash'];
            if (password_verify($password, $pass_hash)) {
                $scMess ="Привет, ".$row[0]['LastName']." ".$row[0]['FirstName']." ".$row[0]['patronymic'];
                $_SESSION['user_id'] = $row[0]['ID'];
                header("Location: UserDashboard.php");
            } else $flMess =  'Пароль неверный!';

        } else $flMess = 'Email не существует!';
    }
    catch (PDOException $e)
    {
        $flMess = 'Ошибка Базы Данных!';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="./css/signin.css" rel="stylesheet">
    <title>Курсы Java. Вход</title>
</head>
<body class="text-center">

<form class="form-signin" method="POST">
    <img class="mb-4" src="./assets/logo.png" alt="" width="97" height="178">
    <h1 class="h3 mb-3 font-weight-normal">Вход для зарегистрированных пользователей</h1>
    <?php if(isset($scMess)){?><div class="alert-success" role="alert"><?php echo $scMess; ?></div><?php }?>
    <?php if(isset($flMess)){?><div class="alert-danger" role="alert"><?php echo $flMess; ?></div><?php }?>
    <div class="row">
        <label for="inputEmail">Email</label>
        <input type="email" name="inputEmail" class="form-control" placeholder="" required autofocus>
    </div>
    <p></p>
    <div class="row">
        <label for="inputPassword">Пароль</label>
        <input type="Password" name="inputPassword" class="form-control" placeholder="" required>
    </div>

    <div class="checkbox mb-3">
        <label>
            <input type="checkbox" value="remember-me"> Запомнить меня
        </label>
    </div>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
    <button class="btn btn-lg btn-primary btn-block" type="button" onClick='location.href="index.php"'>На главную</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2020</p>
</form>
<?php
require ('Disconnect.php');
?>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
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
</html>