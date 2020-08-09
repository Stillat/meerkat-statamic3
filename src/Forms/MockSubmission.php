<?php

namespace Stillat\Meerkat\Forms;

use Statamic\Forms\Submission;

/**
 * Class MockSubmission
 *
 * Wraps Meerkat data in a Statamic Submission container
 * to provide compatibility with the native form event.
 *
 * @package Stillat\Meerkat\Forms
 * @since 2.0.0
 */
class MockSubmission extends Submission
{

    public function __construct()
    {
        $this->form = null;
    }

    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Mocks the save method.
     */
    public function save()
    {
        return;
    }

    /**
     * Mocks the delete method.
     */
    public function delete()
    {
        return;
    }

}
