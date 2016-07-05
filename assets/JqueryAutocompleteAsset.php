<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 05.07.16
 * Time: 12:12
 */

namespace voenniy\jsonrpc\assets;


use yii\web\AssetBundle;

class JqueryAutocompleteAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-textcomplete/dist';

    public $js = [
        'jquery.textcomplete.min.js'
        ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}