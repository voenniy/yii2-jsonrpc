<?php
namespace voenniy\jsonrpc\components;
use voenniy\jsonrpc\JsonRPCModule;
use Yii;
use yii\helpers\VarDumper;
use yii\web\HttpException;

class Action extends \yii\base\Action
{
    use traits\Serializable;

    public $debug = false;

    public function run()
    {
        $this->failIfNotAJsonRpcRequest();
        Yii::beginProfile('service.request');
        $output = null;
        try {
            if($this->debug){
                $command = Yii::$app->request->get('command');
                preg_match("/([^\(]*)\((.*)\)/", $command, $parsed);
                $method = @$parsed[1] ? $parsed[1] : $command;

                $this->setMethod($method);
                if($parsed[2] !== ''){
                    $this->setParams(explode(",", @$parsed[2]));
                } else {
                    $this->setParams([]);
                }

            } else {
                $this->setRequestMessage(file_get_contents('php://input'));
            }
            $this->result = $this->tryToRunMethod();
        } catch (\Exception $e) {
            Yii::error($e, 'service.error');
            $this->exception = new Exception($e->getMessage(), Exception::INTERNAL_ERROR);
        }
        Yii::endProfile('service.request');
        if($this->debug){
            $output = isset($this->toArray()['error']) ? $this->toArray()['error'] : $this->toArray()['result'];
            return VarDumper::dumpAsString($output, 10, true);
        } else {
            return $this->toJson();
        }


    }
    /**
     * @return mixed
     * @throws Exception
     */
    protected function getHandler()
    {
        if(strpos($this->getMethod(), '.') !== false){
            list($object, $method) = explode(".", $this->getMethod());
            $object = Yii::createObject(JsonRPCModule::getInstance()->apiNamespace . '\\' . $object);
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
            throw new Exception("Method not found " . $this->getMethod(), Exception::METHOD_NOT_FOUND);
        }
        $method = $class->getMethod($this->getMethod());

        return $method;
    }

    /**
     * @param string|callable|\ReflectionMethod $method
     * @param array $params
     *
     * @return mixed
     */
    protected function runMethod($method, $params)
    {
        if(is_string(key($params))){
            // именнованные ключи
            $params = $this->namedParams($method, $params);
        }
        return $method->invokeArgs($this->getObject(), $params);
    }

    /**
     * Pass method arguments by name
     * @param $method
     * @param array $arguments
     * @return array
     * @throws Exception
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
                throw new Exception("Missing required argument #" . ($param->getPosition()+1) . ", " . $name, Exception::INVALID_PARAMS);
            }

            $values[$param->getPosition()] =
                $isArgumentGiven ? $arguments[$name] : $param->getDefaultValue();

        }

        // Если передано имя аргумента, которого нет в методе
        foreach ($arguments as $aName=>$v) {
            if(array_search($aName, $names) === false){
                throw new Exception("Argument " . $aName . " not exists in method", Exception::INVALID_PARAMS);
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
        if (!((Yii::$app->request->isPost || Yii::$app->request->isOptions) && $this->checkContentType()) && !$this->debug) {
            throw new HttpException(404, "Page not found!");
        }
    }


}