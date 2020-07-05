<?php

namespace Stillat\Meerkat\Tags;

use Statamic\Tags\Tags;
use Stillat\Meerkat\Addon as MeerkatAddon;

class Meerkat extends Tags
{

    public function index()
    {
        return '';
    }


    public function version()
    {
        return MeerkatAddon::VERSION;
    }

}