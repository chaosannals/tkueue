<?php

namespace app\controller;

use tkueue\attribute\Permission;
use tkueue\BaseController;

#[Permission('debug')]
class Debug extends BaseController
{
    /**
     * 调试用加密。
     * 
     */
    public function encrypt()
    {
        $key = $this->request->param('key');
        $data = $this->request->param('data');
        $result = aes256_encrypt($key, $data);
        return json([
            'result' => $result,
        ]);
    }

    /**
     * 调试用解密。
     * 
     */
    public function decrypt()
    {
        $key = $this->request->param('key');
        $data = $this->request->param('data');
        $result = aes256_decrypt($key, $data);
        return json([
            'result' => $result,
        ]);
    }
}
