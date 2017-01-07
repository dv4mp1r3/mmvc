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
                    if (data['id'] > 0)
                        $('a[video_id='+data.id+']').parent().parent().remove();
                    if (data['current'])
                        nextVideo();
                }
                
            }
        });
}

var timerId = setInterval(obs, 1000);

