var span = null;
var current_webm = null;
function nextVideo(isAdmin)
{
    current_webm.removeClass('webm-current');
    current_webm = current_webm.next('div#playlist div.row_video');
    current_webm.addClass('webm-current');
    
    var newUrl = current_webm.attr('orig_url')   
    videoPlayer.src = current_webm.attr('orig_url');
    console.log('new url: '+newUrl);
    
    return true;
}

function addNew(html, url)
{
    $('div#playlist').append(html);
}

function getNewWebm()
{
    var ids = $('div.row_video').map(function(){
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

    $("#webm_player").attr('src', current_webm.attr('orig_url'));

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

