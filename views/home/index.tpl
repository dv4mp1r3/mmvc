<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title></title>
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/1-col-portfolio.css" rel="stylesheet">
    </head>

    <body>
        <div class="container">
            
            <div class="row div-vertical">
                <form class="frm-vertical" method="post" action="?">
                    <input name="video.url" placeholder="Ссылка на видео">
                    <input name="user.name" placeholder="Кто добавил">
                    <input type="submit" value="Добавить">
                </form>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Список загруженных видео
                    </h1>
                </div>
            </div>

            {foreach from=$videos item=video}
                <div class="row">
                    <div class="col-md-7">
                        <a target="_blank" href="{$video.url}">
                            <img class="img-responsive" src="http://img.youtube.com/vi/{$video.unique_id}/hqdefault.jpg" alt="">
                        </a>
                    </div>
                    <div class="col-md-5">
                        <h3>Добавлено: </h3>
                        <h4>{$video.name}</h4>
                       
                        <a class="btn btn-primary" target="_blank" href="{$video.url}">Открыть в новой вкладке <span class="glyphicon glyphicon-chevron-right"></span></a>
                    </div>
                </div>
                <hr>
            {/foreach}
            
            <footer>
                <div class="row">
                    <div class="col-lg-12">
                        <p>Copyright &copy;</p>
                    </div>
                </div>
            </footer>

        </div>
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.min.js"></script>

    </body>

</html>

