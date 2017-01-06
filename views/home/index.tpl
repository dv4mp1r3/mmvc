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

            <div class="row">
                <form id="frm-upload" action="index.php?u=home-upload" method="post">
                    <input name="video.url" placeholder="Ссылка на видео">
                    <input name="user.name" placeholder="Кто добавил">
                    <input type="submit" value="Добавить">
                </form>
            </div>

            <div class="row">
                <a id="btn_skip" class="btn btn-primary" target="_blank" href="#">Пропустить текущее</a>
            </div>
            
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
                        <a video-id="{$video.id}" orig-url="{$video.url}" class="btn btn-primary btn-remove-video" href="#">
                            Удалить из списка
                        </a>
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
                        <p>Copyright &copy;</p>
                    </div>
                </div>
            </footer>

        </div>
        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>

        {literal}
            <script>
                var playlist = {/literal}{$video_urls}{literal};
                var curVideo = 0;
                var videoPlayer = document.getElementById('webm_player');
                videoPlayer.onended = function ()
                {
                    nextVideo();
                }

                function nextVideo()
                {
                    ++curVideo;
                    if (curVideo < playlist.length)
                    {
                        videoPlayer.src = playlist[curVideo];
                        return playlist.length - (curVideo + 1) !== 0;
                    }
                    return false;
                }

                $(document).ready(function () {
                    for (i = 1; i <= playlist.length; i++)
                    {
                        var currentCanvas = document.getElementById('canvas-' + i);
                        if (currentCanvas)
                        {
                            currentCanvas.getContext('2d').drawImage(videoPlayer, 0, 0);
                        }
                    }

                    $("#btn_skip").click(function () {
                        if (!nextVideo())
                        {
                            $(this).removeClass('btn-primary');
                            $(this).removeClass('btn-disabled');
                            $(this).css('cursor', 'arrow');
                        }
                        return false;
                    });

                    $("#frm-upload").submit(function (e) {

                        //e.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: "index.php?u=home-upload",
                            data: $("#frm-upload").serialize(),
                            dataType: 'json',
                            success: function (data)
                            {
                                console.log(data); 
                            }
                        });
                        
                    })
                });

            </script>
        {/literal}

    </body>

</html>

