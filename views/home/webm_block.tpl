<div class="row">
    <div class="col-md-7">
        {if $isAdmin}
            <a video_id="{$video.id}" orig_url="{$video.url}" class="btn btn-primary btn-remove-video" onclick="remove_click({$video.id})">
            Удалить из списка
            </a>
        {/if}
        <p>Добавил: {$video.username}</p>
        <canvas id="canvas-{$video.id}">
        </canvas>
    </div>
</div>
<hr>