<?php


?>
<!DOCTYPE html>
<html>
    <?= file_get_contents(dirname(__FILE__).'/header.html'); ?>
    <body>
        <?php require_once ROOT_DIR.'/views/'.MMVC_CTRL_NAME.'/'.MMVC_CTRL_VIEW.'.php'; ?>
        <?= file_get_contents(dirname(__FILE__).'/footer.html'); ?>
    </body>
</html>