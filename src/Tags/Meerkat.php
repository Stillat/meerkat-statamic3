<?php

namespace Stillat\Meerkat\Tags;

use Statamic\Tags\Tags;
use Stillat\Meerkat\Addon as MeerkatAddon;

class Meerkat extends Tags
{
    use MeerkatResponses;

    public function index()
    {
        return 'asdfasdfasdfasdf';
    }


    public function version()
    {
        return MeerkatAddon::VERSION;
    }

}