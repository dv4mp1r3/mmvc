<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Добро пожаловать, снова">
        <meta name="author" content="dv4mp1r3">
        <title>WebMDJ</title>
        <link href="{$www_root}/assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="{$www_root}/assets/css/1-col-portfolio.css" rel="stylesheet">
        {if $isOBS}
            <link href="{$www_root}/assets/css/obs.css" rel="stylesheet">
        {/if}
    </head>

    <body>
        <div class="container">
            <div class="hidden-for-obs row form-row">
                <form id="frm-upload" action="index.php?u=video-upload" method="post">
                    <div class="row form-row">
                        <input class="input-text" name="video.url" placeholder="Ссылка на видео">
                        <span class="glyphicon" id="ico-status"><span>
                    </div>
                    <div class="row form-row">
                        <input class="input-text" name="user.name" placeholder="Кто добавил">
                        <input type="submit" value="Добавить">
                    </div>                    
                </form>
            </div>
            {if $isAdmin and count($videos)}
                <div class="hidden-for-obs row current-button">
                    <a id="btn_skip" class="btn btn-primary" target="_blank" href="#">Пропустить текущее</a>
                </div>
            {/if}

            <div class="row">
                <video id="webm_player" src="{$videos[0].url}" type="video/webm" {if $isOBS == false}controls{/if}>
                </video>
            </div>

            <div class="hidden-for-obs row">
                <div class="col-lg-12">
                    <h1 class="page-header">
                        {if count($videos)}
                            Список загруженных видео
                        {else}
                            Список пуст
                        {/if}
                    </h1>
                </div>
            </div>

            <div id="playlist" class="hidden-for-obs row">
                {foreach from=$videos item=video}
                    {include file="views/home/webm_block.tpl" video=$video isAdmin=$isAdmin}  

                {/foreach}
            </div>

            <footer class="hidden-for-obs">
                <div class="row">
                    <div class="col-lg-12">
                        <p>Copyright &copy; {$year}</p>
                    </div>
                </div>
            </footer>

        </div>
        
        <script src="{$www_root}/assets/js/jquery.js"></script>
        <script src="{$www_root}/assets/js/bootstrap.min.js"></script>
        <script>
            var playlist = {$video_urls};
            var curVideo = 0;
            var videoPlayer = document.getElementById('webm_player'); 
            videoPlayer.volume = 0.5;
        </script>
        <script src="{$www_root}/assets/js/user_logic.js"></script>
        {if $isAdmin}<script src="{$www_root}/assets/js/admin_logic.js"></script>{/if}
        {literal}
        <script>              
            videoPlayer.onended = function ()
            {
                nextVideo({/literal}{if $isAdmin}1{else}0{/if}{literal});
            }
        </script>
        {/literal}
        </body>

    </html>

