<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Пример на bootstrap 4: Checkout - пользовательская форма заказа, показывающая компоненты формы и функции проверки. Версия v4.0.0">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>Курсы Java. Регистрация</title>

    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="form-validation.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php
    require ('connect.php');


    if (isset($_POST['email']) && isset($_POST['password'])){
        $email = $_POST['email'];
        $LastName = $_POST['LastName'];
        $FirstName = $_POST['FirstName'];
        $patronymic = $_POST['patronymic'];
        $password = $_POST['password'];
        $registered = date("Y-m-d H:i:s");
        $stage = 1;
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $teacher = $_POST['csTeacher'];
        $InGroup = $_POST['csGroup'];


        /*Я всю ночь провозился и так и не понял, как это работает.
         * $sth = $db->prepare("INSERT * INTO jc_students (EMAIL, FirstName, LastName, patronymic, InGroup, teacher, stage, registered, pass_hash)");
        $sth->execute(array(EMAIL => $email, FirstName => $FirstName, LastName => $LastName, patronymic => $patronymic, InGroup => $InGroup,
            teacher => $teacher, stage => $stage, registered => $registered, pass_hash => $pass_hash));*/
        
    }
?>


<div class="container">
    <div class="py-5 text-center">
        <img class="d-block mx-auto mb-4" src="./assets/logo.png" alt="" width="97" height="178">
        <h2>Форма регистрации</h2>
        <p class="lead">
    </div>


            <form class="needs-validation" method="POST" action="" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="LastName">Фамилия</label>
                        <input type="text" class="form-control" name="LastName" placeholder="" value="" required>
                        <div class="invalid-feedback">
                            Введите фамилию.
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="FirstName">Имя</label>
                        <input type="text" class="form-control" name="FirstName" placeholder="" value="" required>
                        <div class="invalid-feedback">
                            Введите имя.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="patronymic">Отчество</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="patronymic" placeholder="" required>
                        <div class="invalid-feedback" style="width: 100%;">
                            Введите отчество.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="inputEmail">Email </label>
                    <input type="email" class="form-control" name="email" placeholder="you@example.com" required>
                    <div class="invalid-feedback">
                        Введите адрес електронной почты.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="inputPassword">Пароль</label>
                    <input type="password" class="form-control" name="password" placeholder="" required>
                    <div class="invalid-feedback">
                        Введите пароль.
                    </div>
                </div>

                <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="csTeacher">Преподаватель</label>
                            <select class="custom-select d-block w-100" name="csTeacher" required>
                                <option value="">Выберите...</option>
                                <?php
                                $sth = $db->prepare("SELECT * FROM jc_teachers");
                                $sth->execute();
                                $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($array as $key => $value) {
                                    print "<option value=\"$value[ID]\">$value[FirstName] $value[patronymic] $value[LastName] </option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                Выберите преподавателя.
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="csGroup">Группа</label>
                            <select class="custom-select d-block w-100" name="csGroup" required>
                                <option value="">Выберите...</option>
                                <?php
                                $sth = $db->prepare("SELECT * FROM jc_groups");
                                $sth->execute();
                                $array = $sth->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($array as $key => $value) {
                                    print "<option value=\"$value[ID]\">$value[Name]</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                Выберите группу.
                            </div>
                        </div>
                </div>


                <hr class="mb-4">
                <button class="btn btn-primary btn-lg btn-block" type="submit">Зарегистрироваться</button>

            </form>


    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">&copy; 2020 Java Course</p>
    </footer>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="/docs/4.0/assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
<script src="/docs/4.0/assets/js/vendor/popper.min.js"></script>
<script src="/docs/4.0/dist/js/bootstrap.min.js"></script>
<script src="/docs/4.0/assets/js/vendor/holder.min.js"></script>
<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
        'use strict';

        window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');

            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>-->
</body>
</html>