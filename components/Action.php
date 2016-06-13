<?php
namespace voenniy\jsonrpc\components;
use voenniy\jsonrpc\components\traits\Request;
use voenniy\jsonrpc\JsonRPCModule;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class Action extends \yii\base\Action
{
    use Request;
    public $debug = false;
    public $result = false;

    public function run()
    {
        $this->failIfNotAJsonRpcRequest();
        Yii::beginProfile('service.request');
        $output = null;
        try {
            if($this->debug){
                $command = Yii::$app->request->get('command');
                try {
                    $r = Json::decode($command);
                    if(!isset($r['method'])){
                        throw new \voenniy\jsonrpc\components\Exception('Method not found', \voenniy\jsonrpc\components\Exception::METHOD_NOT_FOUND);
                    }
                    $this->setMethod($r['method']);
                    if(!isset($r['params'])){
                        $this->setParams([]);
                    } else {
                        $this->setParams($r['params']);
                    }
                } catch (\Exception $e){
                    preg_match("/([^\(]*)\((.*)\)/", $command, $parsed);
                    $method = @$parsed[1] ? $parsed[1] : $command;

                    $this->setMethod($method);
                    if($parsed[2] !== ''){
                        $this->setParams(explode(",", @$parsed[2]));
                    } else {
                        $this->setParams([]);
                    }
                }
            } else {
                $this->setRequestMessage(Yii::$app->request->rawBody);
            }
            $this->result['result'] = $this->tryToRunMethod();
        } 
        catch (\Exception $e) {
            Yii::error($e, 'service.error');
            $this->result = JsonRpcException::convertExceptionToArray($e);
            if(!isset($this->result['type'])){
                $this->result['type'] = 'Exception';
            }
        }
        Yii::endProfile('service.request');
        if($this->debug){
            return VarDumper::dumpAsString($this->result, 10, true);
        } else {
            return $this->result;
        }


    }
    /**
     * @return mixed
     * @throws JsonRpcException
     */
    protected function getHandler()
    {
        if(strpos($this->getMethod(), '.') !== false){
            list($object, $method) = explode(".", $this->getMethod());
            try {
                $object = Yii::createObject(JsonRPCModule::getInstance()->apiNamespace . '\\' . $object);
            } catch (\Exception $e){
                throw new JsonRpcException($e->getMessage(), JsonRpcException::METHOD_NOT_FOUND, $e);
            }

        } else {
            $object = 'Base';
            $method = $this->getMethod();
            $class = get_class($this);
            $namespace = '';
            if (($pos = strrpos($class, '\\')) !== false) {
                $namespace = substr($class, 0, $pos) . '\\';
            }
            $object = Yii::createObject($namespace . $object);
        }
        $this->setMethod($method);
        $this->setObject($object);
        $class = new \ReflectionClass($this->getObject());
        if (!$class->hasMethod($this->getMethod())) {
            throw new JsonRpcException("Method not found " . $this->getMethod(), JsonRpcException::METHOD_NOT_FOUND);
        }
        $method = $class->getMethod($this->getMethod());

        return $method;
    }

    /**
     * @param \ReflectionMethod $method
     * @param $params
     * @return mixed
     * @throws JsonRpcException
     */
    protected function runMethod($method, $params)
    {
        if(is_string(key($params))){
            // именнованные ключи
            $params = $this->namedParams($method, $params);
        }
        try {
            return $method->invokeArgs($this->getObject(), $params);
        } catch (ErrorException $e) {
            throw new JsonRpcException($e->getMessage() . ' ' . get_class($e) . '[method=> ' . $method->getName() . ',  params=> ' . json_encode($params) . ']', JsonRpcException::INVALID_PARAMS);
        }

    }

    /**
     * Pass method arguments by name
     * @param $method
     * @param array $arguments
     * @return array
     * @throws JsonRpcException
     */
    protected function namedParams($method, array $arguments = array())
    {
        Yii::info('Param: ' . get_class($method), 'RPC');
        $values = $names = [];

        // Проверяем, есть ли среди аргументов метода такой аргумент, который пришёл из RPC, а если нету - то пробуем поставить ему дефолтное значение
        foreach($method->getParameters() as $param){
            $name = $param->getName();
            $names[] = $name;
            $isArgumentGiven = array_key_exists($name, $arguments);
            if (!$isArgumentGiven && !$param->isDefaultValueAvailable()) {
                throw new JsonRpcException("Missing required argument #" . ($param->getPosition()+1) . ", " . $name, JsonRpcException::INVALID_PARAMS);
            }

            $values[$param->getPosition()] =
                $isArgumentGiven ? $arguments[$name] : $param->getDefaultValue();

        }

        // Если передано имя аргумента, которого нет в методе
        foreach ($arguments as $aName=>$v) {
            if(array_search($aName, $names) === false){
                throw new JsonRpcException("Argument " . $aName . " not exists in method", JsonRpcException::INVALID_PARAMS);
            }
        }


        return $values;
    }

    protected function tryToRunMethod()
    {
        $method = $this->getHandler();
        Yii::beginProfile('service.request.action');
        $output = $this->runMethod($method, $this->getParams($method));
        Yii::endProfile('service.request.action');
        Yii::info($method, 'service.output');
        Yii::info($output, 'service.output');

        return $output;
    }

    protected function failIfNotAJsonRpcRequest()
    {
        if(!$this->isJsonRpcRequest() && !$this->debug){
            throw new BadRequestHttpException("Invalid JSON-RPC data in request. The request must POST or OPTION and Content-Type=application/json");
        }
    }

    public static function isJsonRpcRequest()
    {
        if(Yii::$app->request->isPost || Yii::$app->request->isOptions){
            return true;
        }
        return false;
    }



}