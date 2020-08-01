<?php

namespace Stillat\Meerkat\Parsing\Sanitizers;

use Stillat\Meerkat\Core\Contracts\Parsing\OutputSanitizerContract;

class AntlersSanitizer implements OutputSanitizerContract
{

    /**
     * Sanitizes the input value.
     *
     * @param string $value The value to sanitize.
     * @return string
     */
    public function sanitize($value)
    {
        $value = str_replace('{', '&#123;', $value);
        $value = str_replace('}', '&#125;', $value);

        return $value;
    }

}
