var span = null;
var current_webm = null;
function nextVideo(isAdmin)
{
    current_webm.removeClass('webm-current');
    current_webm = current_webm.next('div#playlist div.row');
    current_webm.addClass('webm-current');
       
    ++curVideo;
    if (curVideo < playlist.length)
    {
        videoPlayer.src = playlist[curVideo]
        return playlist.length - (curVideo + 1) !== 0;
    }
    return false;
}

function addNew(html, url)
{
    $('div#playlist').append(html);
    playlist.push(url);
}

function getNewWebm()
{
    var ids = $('div.hidden-for-obs .col-md-7 a.btn-remove-video').map(function(){
                return $(this).attr('video_id')
            }).get().join(',');
    console.log('tick: '+ids);        
    $.ajax({
            type: "POST",
            url: "/mmvc/video/getnew",
            data: {old_ids: ids},
            dataType: 'json',
            success: function (data)
            {
                console.log(data);
                if (data.error === 0 && data.data.length > 0)
                {
                    $('h1.page-header').html('Список загруженных видео');
                    for (i = 0; i < data.data.length; i++)
                    {
                        addNew(data.data[i].html, data.data[i].url);
                    }
                }
            }
        });
}

$(document).ready(function () {
    current_webm = $('div#playlist div.row').first();
    current_webm.addClass('webm-current');
        
    var timerId = setInterval(getNewWebm, 5000);
    //getNewWebm();
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
                addNew(data.data.html, data.data.url);
                if (span === null)
                    span = $('span#ico-status');
                span.removeClass('glyphicon-remove');
                span.addClass('glyphicon-ok');
                console.log(data);
            }
        });

    })
});

