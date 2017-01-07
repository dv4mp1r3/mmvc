var span = null;
var current_webm = null;
function nextVideo(isAdmin)
{
    current_webm.removeClass('webm-current');
    current_webm = current_webm.next('div#playlist div.row');
    current_webm.addClass('webm-current');
    
    if (isAdmin)
        $.ajax({
            type: "POST",
            url: "/mmvc/video/update",
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
        if (isAdmin)
            videoPlayer.play();
        return playlist.length - (curVideo + 1) !== 0;
    }
    return false;
}

$(document).ready(function () {
    current_webm = $('div#playlist div.row').first();
    current_webm.addClass('webm-current');
    
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
        var video_url = $('input[name="video.url"]').val();
        if (!/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*).webm/.test(video_url))
        {
            console.log("Неправильная ссылка на WebM ("+video_url+")");
            if (span === null)
                span = $('span#ico-status');
            span.removeClass('glyphicon-ok');
            span.addClass('glyphicon-remove');
            return false;
        }
        
        $.ajax({
            type: "POST",
            url: "/mmvc/video/upload",
            data: $("#frm-upload").serialize(),
            dataType: 'json',
            success: function (data)
            {
                $('h1.page-header').html('Список загруженных видео');
                $('div#playlist').append(data.data.html);
                playlist.push(data.data.url);
                if (span === null)
                    span = $('span#ico-status');
                span.removeClass('glyphicon-remove');
                span.addClass('glyphicon-ok');
                console.log(data);
            }
        });

    })
});

