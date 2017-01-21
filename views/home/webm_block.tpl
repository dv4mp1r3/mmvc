<div video_id="{$video.id}" orig_url="{$video.url}" class="hidden-for-obs row row_video">
    <div class="col-md-7">
        {if $isAdmin}
            <a video_id="{$video.id}" orig_url="{$video.url}" class="btn btn-primary btn-remove-video" onclick="remove_click({$video.id})">
            Удалить из списка
            </a>
        {/if}
        <p>Добавил: {$video.username}</p>
        <p>Ссылка: <a href="{$video.url}" target="_blank">{$video.url}</a></p>
        {*<canvas id="canvas-{$video.id}">
        </canvas>*}
    </div>
</div>