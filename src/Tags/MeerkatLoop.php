<?php

namespace Stillat\Meerkat\Tags;

use Statamic\Facades\Antlers;
use Statamic\Support\Arr;

trait MeerkatLoop
{


    protected function parseMeerkatLoop($data, $supplement = true)
    {
        if ($as = $this->params->get('as')) {
            return $this->parse([$as => $data]);
        }

        if ($scope = $this->get('scope')) {
            $data = Arr::addScope($data, $scope);
        }

        return Antlers::usingParser($this->parser, function ($antlers) use ($data, $supplement) {
            return $antlers
                ->parseLoop($this->content, $data, $supplement, $this->context->all())
                ->withoutExtractions();
        });
    }


}