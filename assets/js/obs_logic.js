var timerId = null;

function obs()
{
    $.ajax({
            type: "POST",
            url: "/mmvc/video/obs/obs/true",
            //data: {video_id: video_id},
            dataType: 'json',
            success: function (data)
            {
                console.log(data);
                if (data.error == 0)
                {
                    if (data['id'] !== undefined)
                    {
                        var currentId = parseInt($(current_webm).find('a').attr('video_id'));
                        if (currentId == data['id'])
                        {
                            nextVideo();
                            videoPlayer.play();
                        }
                        $('a[video_id='+data.id+']').parent().parent().remove(); 
                    }
                    else if (data['current'] != undefined && data['current'] === true) 
                    {
                        nextVideo();
                        videoPlayer.play();
                    }
                }
                else
                {
                    //clearInterval(timerId);
                }
                
            }
        });
}

timerId = setInterval(obs, 1000);

