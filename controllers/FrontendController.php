<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 11/23/15
 * Time: 5:10 PM
 */

namespace common\modules\jsonrpc\controllers;


use yii\web\Controller;

class FrontendController extends Controller
{
    public $enableCsrfValidation = false;

    public function actions()
    {
        return array(
            'index' => array(
                'class' => 'common\modules\jsonrpc\components\Action',
            ),
        );
    }

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
            ],
        ];
    }

}