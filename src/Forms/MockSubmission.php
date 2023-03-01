<?php

namespace Stillat\Meerkat\Forms;

use Statamic\Forms\Submission;

/**
 * Class MockSubmission
 *
 * Wraps Meerkat data in a Statamic Submission container
 * to provide compatibility with the native form event.
 *
 * @since 2.0.0
 */
class MockSubmission extends Submission
{
    public function __construct()
    {
        parent::__construct();

        $this->form = new MockForm();
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

    }

    /**
     * Mocks the delete method.
     */
    public function delete()
    {

    }
}
