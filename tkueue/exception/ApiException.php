<?php

namespace tkueue\exception;

use think\exception\HttpResponseException;

class ApiException extends HttpResponseException
{
    public function __construct($key, $data = [])
    {
        parent::__construct(respond($key, $data));
    }
}
