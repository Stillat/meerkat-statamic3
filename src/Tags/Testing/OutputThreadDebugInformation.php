<?php

namespace Stillat\Meerkat\Tags\Testing;

use Stillat\Meerkat\Tags\MeerkatTag;

class OutputThreadDebugInformation extends MeerkatTag
{

    public function render()
    {
        return view('meerkat::tags.debug.thread');
    }

}
