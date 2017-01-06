<?php

namespace app\controllers;

use app\models;
use app\core\AccessChecker;

class HomeController extends BaseController {

    public function __construct() {
        $this->rules = [
            'index' => [
                AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
            'info' => [
                AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
        ];
        parent::__construct();
    }

    public function actionIndex() {
        $isOBS = isset($_REQUEST['obs']) && $_REQUEST['obs'] === 'true';
        $isAdmin = isset($_REQUEST['admin']) && $_REQUEST['admin'] === 'true';
        if ($isAdmin === true) {
            $_SESSION['auth'] = true;
        } else {
            unset($_SESSION['auth']);
        }


        $videos = models\Video::select(['user.name username', 'video.url', 'video.id', 'video.is_viewed'])->
                join(models\Video::JOIN_TYPE_LEFT, 'user', 'user.id = video.user_id')->
                where('video.is_viewed = 0')->
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
        $this->render('index');
    }

    public function actionInfo() {
        phpinfo();
    }

}
