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
<?php
    session_start();
    require ('connect.php');
    if (isset($_POST['inputEmail']) and isset($_POST['inputPassword'])){
        $sql = "SELECT * FROM jc_students WHERE `email`=:email ";
        $sth = $db->prepare($sql);
        $sth->bindValue(':email', $_POST['inputEmail']);
        $sth->execute( );
        $array = $sth->fetchAll(PDO::FETCH_ASSOC);
        $phpgovno =  $array[0];
        if(count($array)>0){
            if (password_verify($_POST['inputPassword'], $phpgovno['pass_hash'])){
                echo 'Привет ', $phpgovno['LastName'], ' ', $phpgovno['FirstName'], ' ',$phpgovno['patronymic'];
            }else echo  'Пароль неверный';

        }else echo 'Email не существует';




    }


?>
<form class="form-signin" method="POST">
    <img class="mb-4" src="./assets/logo.png" alt="" width="97" height="178">
    <h1 class="h3 mb-3 font-weight-normal">Вход для зарегистрированных пользователей</h1>
    <div class="row">
        <label for="inputEmail">Email</label>
        <input type="email" name="inputEmail" class="form-control" placeholder="" required autofocus>
    </div>
    <p></p>
    <div class="row">
        <label for="inputPassword">Пароль</label>
        <input type="password" name="inputPassword" class="form-control" placeholder="" required>
    </div>

    <div class="checkbox mb-3">
        <label>
            <input type="checkbox" value="remember-me"> Запомнить меня
        </label>
    </div>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
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
</body>
</html>