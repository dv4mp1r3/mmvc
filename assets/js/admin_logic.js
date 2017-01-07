
var dom_element;

var remove_click = function (video_id) {
        var dom_element = $('a[video_id='+video_id+']');
        $.ajax({
            type: "POST",
            url: "/mmvc/video/remove",
            data: {video_id: video_id},
            dataType: 'json',
            success: function (data)
            {
                console.log(data);
                if (data.error === 0)
                    dom_element.parent().parent().remove();
            }
        });
       
    };

$(document).ready(function () {
    $("#btn_skip").click(function () {
        if (!nextVideo(true))
        {
            $(this).removeClass('btn-primary');
            $(this).removeClass('btn-disabled');
            $(this).css('cursor', 'arrow');
        }
        return false;
    });
});


