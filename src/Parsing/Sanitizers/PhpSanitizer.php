<?php

namespace Stillat\Meerkat\Parsing\Sanitizers;

use Stillat\Meerkat\Core\Contracts\Parsing\OutputSanitizerContract;

class PhpSanitizer implements OutputSanitizerContract
{

    /**
     * Sanitizes the input value.
     *
     * @param string $value The value to sanitize.
     * @return string
     */
    public function sanitize($value)
    {
        $value = str_replace('<?php', '&lt;&#63;php', $value);
        $value = str_replace('<?=', '&lt;&#63;&#61;', $value);

        return $value;
    }

}
