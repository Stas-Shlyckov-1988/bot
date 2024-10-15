<?php if(!isset($_SESSION)) session_start(); ?>
<!doctype html>
<html lang="en">
<html>
    <head>
        <title>Чат бот</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
       
        <style>
            .callout, .callout-info {
                border-bottom-width: 2px; /* Толщина линии внизу */
                border-bottom-style: solid; /* Стиль линии внизу */
                border-bottom-color: white; /* Цвет линии внизу */
                font-size: .5em !important;
            }
            .callout {
                background-color: #379d8382;
            }
            .callout-info {
                background-color: #00ffcbd4;
            }
            .callout-warning {
                background-color: red;
                padding: 10px;
            }
            input[name=username] {
                border-bottom: 1px solid gray;
            }
            .username_apply, .username_apply:hover, .username_apply:active {
                display: block;
                float: right;
                margin-top: -20px;
                text-decoration: none
            }
        </style>
        <link href="node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

        <?php if(isset($_GET['username'])): ?>
        <script> window.username = "<?= $_GET['username'] ?>"; </script>
        <?php endif; ?>
    </head>
    <body>
        <div class="container" style="width: 55%;">
        <?php if(isset($_SESSION['user'])): ?>
            <h1>Чат бот</h1>
           
            <div class="mb-3 username_panel">
                <input type="text" class="form-control-plaintext" value="<?= isset($_GET['username']) ? $_GET['username'] : '' ?>" placeholder="Имя получателя" name="username">
                <a href="#" class="username_apply">Подтвердить</a>
            </div>
            <div class="mb-3">
                <label for="exampleFormControlTextarea1" class="form-label">Написать от бота</label>
                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                <!--input class="form-control" type="file" id="formFile" multiple style="display: none;"-->
            </div>
            <div class="col-auto">
                <input class="form-check-input" type="checkbox" value="" id="flexCheckGroup">
                <label class="form-check-label" for="flexCheckChecked">
                    Отправить в группу
                </label>
                
                <button type="submit" class="btn btn-primary mb-3 send-message">Отправить</button>
                <button type="submit" class="btn btn-secondary mb-3 ban_user">Бан</button>
                <button type="submit" class="btn btn-success mb-3 save_messages">Сохранить переписку</button>
            </div>
        <?php else: ?>
            <h1>Авторизоваться</h1>
            <form class="row g-3" action="/scripts/auth.php" method="post">
                <div class="col-auto">
                    <label for="staticEmail2" class="visually-hidden">Логин</label>
                    <input type="text" class="form-control-plaintext" id="staticEmail2" value="" placeholder="Логин" name="login">
                </div>
                <div class="col-auto">
                    <label for="inputPassword2" class="visually-hidden">Пароль</label>
                    <input type="password" class="form-control" id="inputPassword2" placeholder="Пароль" name="password">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mb-3">Отправить</button>
                </div>
            </form>
        <?php endif; ?>
        </div>
        <script src="node_modules/jquery/dist/jquery.min.js"></script>
        <script src="/js/main.js"></script>
        <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    </body>
</html>