<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 13.11.14
 * Time: 18:22
 */

namespace voenniy\jsonrpc\components;


use yii\helpers\Json;

class JsonRPC  {
    protected $method = [];

    public function __call($method, $params){
        // Если передан ассоциативный массив, то берем только первый элемент, в нём будут содержаться именнованые параметры
        if(($namedMethod = str_replace("__named", "", $method)) != $method){
            $method = $namedMethod;
            $params = current($params);
        }
        $this->method = ['method'=>$method, 'params' => $params];
    }

    public static function toString($method, $params){
        $method = ['method' => $method, 'params' => $params];
        return self::generate($method);
    }

    public static function toArray($method, $params){
        $method = ['method' => $method, 'params' => $params];
        return self::generate($method, false);
    }

    protected static function generate($method, $asString = true){
        $request = [];
        if(!isset($method['params'])){
            $method['params'] = [];
        }
        if(!is_array($method['params'])){
            $method['params'] = [$method['params']];
        }
        if($method) {
            $request = [
                'jsonrpc' => '2.0',
                'method' => $method['method'],
                'params' => $method['params'],
                'id' => uniqid()
            ];
        }
        return $asString ? Json::encode($request) : $request;
    }

    public function __toString(){
        $s = self::generate($this->method);
        $this->method = null;
        return $s;
    }
} 