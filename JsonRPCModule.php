<?php

namespace voenniy\jsonrpc;

use voenniy\jsonrpc\assets\JsonrpcAsset;
use yii\helpers\ArrayHelper;

class JsonRPCModule extends \yii\base\Module
{
    public  $apiNamespace = 'frontend\APIv1';

    public function init()
    {
        parent::init();

        \Yii::$app->response->formatters =  ArrayHelper::merge(\Yii::$app->response->formatters, ['jsonrpc' => 'voenniy\jsonrpc\components\JsonrpcResponseFormatter']);

        \Yii::setAlias('@jsonrpc', __DIR__);

        $view = \Yii::$app->getView();
        JsonrpcAsset::register($view);
    }

    public function getApiPath()
    {
        return rtrim(\Yii::getAlias('@' . str_replace('\\', '/', $this->apiNamespace)), '/') . '/';
    }
}
