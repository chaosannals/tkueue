<?php
use tkueue\ExceptionHandle;
use tkueue\HttpRequest;

// 容器Provider定义文件
return [
    'think\Request'          => HttpRequest::class,
    'think\exception\Handle' => ExceptionHandle::class,
];
