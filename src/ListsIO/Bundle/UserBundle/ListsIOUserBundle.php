<?php

namespace ListsIO\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ListsIOUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
