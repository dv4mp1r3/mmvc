<?php

namespace mmvc\controllers;

use mmvc\models;
use mmvc\core\AccessChecker;
use mmvc\models\data\RDBHelper;

class HomeController extends WebController {

    public function __construct() {
        $this->rules = [
            'index' => [
                self::RULE_TYPE_ACCESS_GRANTED => AccessChecker::USER_ALL,
                self::RULE_TYPE_INPUT => self::INPUT_PARAMETER_REQUEST,
            ],
            'info' => [
                self::RULE_TYPE_ACCESS_GRANTED => AccessChecker::USER_ALL,
                self::RULE_TYPE_INPUT => self::INPUT_PARAMETER_REQUEST,
            ],
        ];
        parent::__construct();
    }

    public function actionIndex() {
        //var_dump($_COOKIE);
        $isOBS = isset($_REQUEST['obs']) && $_REQUEST['obs'] === 'true';
        $isAdmin = isset($_REQUEST['admin']) && $_REQUEST['admin'] === 'true';
        if ($isAdmin === true) {
            $_SESSION['auth'] = true;
        } else {
            unset($_SESSION['auth']);
        }
        if ($isOBS === true)
            $_SESSION['obs'] = [];
        
        $videos = models\Video::select(['user.name username', 'video.url', 'video.id', 'video.is_viewed'])->
                join(models\Video::JOIN_TYPE_LEFT, 'user', 'user.id = video.user_id')->
                where('video.is_viewed = :viewed', ['viewed' => 1])->
                execute();
        $data = array();

        $video_urls = array();
        foreach ($videos as &$video) {
            $element = $video->asArray();
            $element['unique_id'] = $video->getVideoId();
            array_push($data, $element);
            array_push($video_urls, $video->url);
        }
        $this->appendVariable('videos', $data);
        $this->appendVariable('video_urls', json_encode($video_urls));
        $this->appendVariable('isAdmin', $isAdmin);
        $this->appendVariable('isOBS', $isOBS);
        $this->appendVariable('year', date('Y'));
        $this->appendVariable('www_root', $this->getHttpRootPath());
        $this->render('index');
    }
    
    public function actionInfo() {
        phpinfo();
    }

}
