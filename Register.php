<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="./css/signin.css" rel="stylesheet">
    <title>Курсы Java. Регистрация</title>
</head>
<body class="text-center">
<?php
    require ('connect.php');

if (isset($_POST['inputEmail']) && isset($_POST['inputPassword'])){
        $email = $_POST['inputEmail'];
        $user_firstname = $_POST['user_firstname'];
        $user_lasttname = $_POST['user_lastname'];
        $user_parname = $_POST['user_parname'];
        $password = $_POST['inputPassword'];
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $is_teacher = 0;

    /*$quary = "INSERT INTO users (EMAIL, FirstName, LastName, patronymic, InGroup, teacher, stage, registered, pass_hash) VALUES ('$email', '$user_firstname', '$user_lasttname', '$user_parname',
    , '$pass_hash')";
    $result = mysqli_query($connection, $quary);

    if ($result){
        $smsg = "Регистрация прошла успешно";
    } else {
        $fsmsg = "Ошибка";
    }*/
    }
?>
<form class="form-singup" method="POST" action="Register.php">
    <img class="mb-4" src="./assets/jde.png" alt="" width="72" height="72">
    <h1 class="h3 mb-3 font-weight-normal">Регистрация</h1>
    <?php if(isset($smsg)){?><div class="alert-success" role="alert"><?php echo $smsg; ?></div><?php }?>
    <?php if(isset($fsmsg)){?><div class="alert-danger" role="alert"><?php echo $fsmsg; ?></div><?php }?>
    <label for="csGroup">Группа</label>
    <select class="form-control" name="csGroup">
        <?php
        $sth = $db->prepare("SELECT * FROM jc_groups");
        $sth->execute();
        $array = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($array as $key => $value) {
            print "<option value=\"$value[ID]\">$value[Name]</option>";
        }
        ?>
    </select>
    <label for="csGroup">Преподаватель</label>
    <select class="form-control" name="csTeacher">
        <?php
        $sth = $db->prepare("SELECT * FROM jc_teachers");
        $sth->execute();
        $array = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($array as $key => $value) {
            print "<option value=\"$value[ID]\">$value[FirstName] $value[LastName] </option>";
        }
        ?>
    </select>
    <label for="inputEmail" class="sr-only">Email адрес</label>
    <input type="email" name="inputEmail" class="form-control" placeholder="Email адрес" required autofocus>
    <label for="inputFirstName" class="sr-only">Имя</label>
    <input type="user_firstname" name="user_firstname" class="form-control" placeholder="Имя" required autofocus>
    <label for="inputParName" class="sr-only">Отчество</label>
    <input type="user_parname" name="user_parname" class="form-control" placeholder="Отчество" required autofocus>
    <label for="inputLastName" class="sr-only">Фамилия</label>
    <input type="user_lasttname" name="user_lastname" class="form-control" placeholder="Фамилия" required autofocus>
    <label for="inputPassword" class="sr-only">Пароль</label>
    <input type="password" name="inputPassword" class="form-control" placeholder="Пароль" required>

    <button class="btn btn-lg btn-primary btn-block" type="submit">Зарегистрироваться</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2020</p>
</form>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>