<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 11/23/15
 * Time: 5:10 PM
 */

namespace voenniy\jsonrpc\controllers;


use yii\web\Controller;

class FrontendController extends Controller
{
    public $enableCsrfValidation = false;

    public function actions()
    {
        return array(
            'index' => array(
                'class' => 'voenniy\jsonrpc\components\Action',
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