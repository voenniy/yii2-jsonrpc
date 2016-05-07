<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 06.05.16
 * Time: 16:10
 */

namespace voenniy\jsonrpc\components;


use yii\base\Component;
use yii\helpers\Json;
use yii\web\ResponseFormatterInterface;

class JsonrpcResponseFormatter extends Component implements ResponseFormatterInterface
{
    public function format($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        $response->setStatusCode(200);

        $request = json_decode(\Yii::$app->request->rawBody, true);
        $answer  = [
            'jsonrpc' => '2.0',
            'id'      => isset($request['id']) ? $request['id'] : $this->newId(),
        ];
        if(is_string($response->data)){
            $response->data = (new JsonRpcException($response->data, JsonRpcException::INTERNAL_ERROR))->getErrorAsArray();
        } elseif(!is_array($response->data)){
            $response->data = (new JsonRpcException(serialize($response->data), JsonRpcException::INTERNAL_ERROR))->getErrorAsArray();
        }
        // Exception
        if(isset($response->data['result'])) {
            $answer['result'] = $response->data['result'];
        } else {
            $answer['error'] = [
                'code'    => JsonRpcException::INTERNAL_ERROR,
                'message' => $response->data['message']
            ];
            if(strpos($response->data['type'], 'UnauthorizedHttpException') !== false){
               $answer['error']['code'] = JsonRpcException::UNAUTHORIZED;
            }
            elseif(strpos($response->data['type'], 'JsonRpcException') !== false){
               $answer['error']['code'] = $response->data['code'];
            }

            if(isset($response->data['status'])){
                $response->setStatusCode($response->data['status'], $response->data['name']);
            }

            if(YII_DEBUG){
                $answer['error']['data'] = $response->data;
            }
        }

        $response->content = Json::encode($answer);
        return $response;
    }

    protected function newId(){
        return \Yii::$app->security->generateRandomString();
    }

}