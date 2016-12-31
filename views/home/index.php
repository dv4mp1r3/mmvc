<?php

namespace app\views;

global $view_variable;
?>
<script>
    $(document).ready(function () {
        var form = $('#review_upload');
        form.submit(function (e) {
            e.preventDefault();
            var actionurl = e.currentTarget.action;
            $.ajax({
                url: actionurl,
                type: 'post',
                dataType: 'json',
                data: form.serialize(),
                success: function (data)
                {
                    form.css('display', 'none');
                    $('#review_thanks').css('display', 'block');
                    console.log('Success: ' + data);
                },
                error: function (err)
                {
                    console.log('Error: ' + err);
                },
            })
            return false;
        });

        $('#upload_avatar').click(function () {

        })
    });
</script>

<div class="container">
    <div class="row">
<?php foreach ($view_variable as $review) { ?>            
            <div class="col-lg-6">
                <p><?= $review->name ?></label></p>
                <p><?= $review->email ?></p>
                <p><?= $review->text ?></p>
            </div>
            <div class="col-lg-6">
                <img src="uploads/<?= $review->avatar ?>"/>
            </div>
<?php } ?>
    </div>
    <div class="row">
        <form id="review_upload" method="post" action="index.php?u=home-upload">
            <div class="col-lg-4">
                <div class="row">
                    <input type="text" name="name" placeholder="Name"/>
                </div>
                <div class="row">
                    <input type="text" name="email" placeholder="Email"/>
                </div>
                <div class="row">
                    <input type="text" name="text" placeholder="text"/>
                </div>

            </div>
            <div class="col-lg-4">
                <img id="upload_avatar"></img>
                <input id="sortpicture" type="file" name="sortpic" />
            </div>
            <div class="col-lg-4">
                <input type="submit" value="Upload"/>
            </div>

        </form>
        <div id="review_thanks" style="display: none;">Thanks for review. It was sent</div>
    </div>
</div>