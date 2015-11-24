<?php

namespace common\modules\jsonrpc\assets;

use yii\web\AssetBundle;

class JsonrpcAsset extends AssetBundle {
    public $sourcePath = __DIR__ .'/js';

    public $js = [
        'rpc_callback.js',
        'jquery.jsonrpc.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
} 