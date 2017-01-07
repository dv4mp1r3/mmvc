function nextVideo(isAdmin)
{
    if (isAdmin)
        $.ajax({
            type: "POST",
            url: "index.php?u=video-update",
            data: {url: playlist[curVideo], video_id: curVideo + 1},
            dataType: 'json',
            success: function (data)
            {
                console.log(data);
            }
        });
    ++curVideo;
    if (curVideo < playlist.length)
    {
        videoPlayer.src = playlist[curVideo];
        return playlist.length - (curVideo + 1) !== 0;
    }
    return false;
}

$(document).ready(function () {
    for (i = 0; i < playlist.length; i++)
    {
        var currentCanvas = document.getElementById('canvas-' + (i + 1));
        if (currentCanvas)
        {
            $("#webm_player").attr('src', playlist[i]);
            var vp = document.getElementById('webm_player');
            currentCanvas.getContext('2d').drawImage(vp, 0, 0);
        }

    }
    $("#webm_player").attr('src', playlist[0]);

    $("#frm-upload").submit(function (e) {

        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "index.php?u=video-upload",
            data: $("#frm-upload").serialize(),
            dataType: 'json',
            success: function (data)
            {
                $('h1.page-header').html('Список загруженных видео');
                $('div#playlist').append(data.data.html);
                playlist.push(data.data.url);
                console.log(data);
            }
        });

    })
});

