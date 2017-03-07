<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link href="{$www_root}/assets/css/ex-details.css" rel="stylesheet">
    </head>
    <body>
        <div class="ex-msg">
            <p>Error:</p>
            <p>{$exceptionMessage}</p>            
        </div>
        <div class="ex-stacktrace">
            <p>Stack trace:</p>
            {$stackTrace}
        </div>
    </body>
</html>