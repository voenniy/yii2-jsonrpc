<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 13.11.14
 * Time: 14:14
 */

namespace voenniy\jsonrpc\controllers;

use yii\web\Controller;
use yii\web\View;

class BackendController extends Controller{

    public function actionIndex(){
        $this->view->registerJs("var API_URL = '" . $this->module->api_url . "'", View::POS_HEAD);
        return $this->render("index");
    }

    public function actions()
    {
        return array(
            'view' => array(
                'class' => 'voenniy\jsonrpc\components\Action',
                'debug' => true
            ),
        );
    }

    public function behaviors()
    {
        return [

        ];
    }
}