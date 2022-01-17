<?php

namespace tkueue\exception;

class TipException extends ApiException
{
    public function __construct($tip, $data = [])
    {
        $data['tip'] = $tip;
        parent::__construct('failed', $data);
    }
}
