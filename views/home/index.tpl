<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Добро пожаловать, снова">
        <meta name="author" content="dv4mp1r3">
        <title>WebMDJ</title>
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/1-col-portfolio.css" rel="stylesheet">
    </head>

    <body>
        <div class="container">
            <div class="row">
                <form id="frm-upload" action="index.php?u=video-upload" method="post">
                    <input name="video.url" placeholder="Ссылка на видео">
                    <input name="user.name" placeholder="Кто добавил">
                    <input type="submit" value="Добавить">
                </form>
            </div>
            {if $isAdmin}
                <div class="row">
                    <a id="btn_skip" class="btn btn-primary" target="_blank" href="#">Пропустить текущее</a>
                </div>
            {/if}

            <div class="row">
                <video id="webm_player" src="{$videos[0].url}" type="video/webm" controls>
                </video>
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
                        {if $isAdmin}
                            <a video_id="{$video.id}" orig_url="{$video.url}" class="btn btn-primary btn-remove-video" href="#">
                            Удалить из списка
                            </a>
                        {/if}
                        <p>Добавил: {$video.username}</p>
                        <canvas id="canvas-{$video.id}">
                        </canvas>
                    </div>
                </div>
                <hr>
            {/foreach}

            <footer>
                <div class="row">
                    <div class="col-lg-12">
                        <p>Copyright &copy; {$year}</p>
                    </div>
                </div>
            </footer>

        </div>
        
        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script>
            var playlist = {$video_urls};
            var curVideo = 0;
            var videoPlayer = document.getElementById('webm_player');  
        </script>
        <script src="assets/js/user_logic.js"></script>
        {if $isAdmin}<script src="assets/js/admin_logic.js"></script>{/if}
        {literal}
        <script>              
            videoPlayer.onended = function ()
            {
                nextVideo({/literal}{$isAdmin}{literal});
            }
        </script>
        {/literal}
        </body>

    </html>

