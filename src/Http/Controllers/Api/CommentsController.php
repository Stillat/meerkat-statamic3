<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;

class CommentsController extends CpController
{

    public function search(CommentResponseGenerator $resultGenerator)
    {
        $resultGenerator->updateFromParameters($this->request->all());

        return $resultGenerator->getApiResponse();
    }

}
