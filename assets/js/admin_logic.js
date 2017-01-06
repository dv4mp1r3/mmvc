
var dom_element;

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
    $("a.btn-remove-video").click(function () {
        dom_element = $(this);
        var video_id = dom_element.attr("video_id");
        $.ajax({
            type: "POST",
            url: "index.php?u=video-remove",
            data: {video_id: video_id},
            dataType: 'json',
            success: function (data)
            {
                console.log(data);
                if (data.error === 0)
                    dom_element.parent().parent().remove();
            }
        });
    });
});


