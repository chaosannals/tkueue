<?php

namespace app\controller;

use tkueue\attribute\Permission;
use tkueue\BaseController;

#[Permission('client')]
class Queue extends BaseController
{
    #[Permission('queue-query')]
    public function query()
    {
    }

    #[Permission('queue-query')]
    public function info()
    {
    }

    #[Permission('queue-push')]
    public function push()
    {
    }

    #[Permission('queue-when')]
    public function when()
    {
    }
}
