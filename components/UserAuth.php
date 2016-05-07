<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 07.05.16
 * Time: 12:22
 */

namespace voenniy\jsonrpc\components;


use yii\filters\auth\AuthMethod;

class UserAuth extends AuthMethod
{
    public function authenticate($user, $request, $response)
    {
        return $user->getIdentity();
    }

}