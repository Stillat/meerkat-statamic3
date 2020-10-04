<?php

namespace Stillat\Meerkat\Core\Logging;

use Stillat\Meerkat\Core\Contracts\Http\HttpClientContract;
use Stillat\Meerkat\Core\UuidGenerator;

/**
 * Class Telemetry
 *
 * Provides interactions with the backend error report telemetry service.
 *
 * @package Stillat\Meerkat\Core\Logging
 * @since 2.0.0
 */
class Telemetry
{

    protected $httpClient = null;

    protected $idGenerator = null;

    public function __construct(HttpClientContract $client, UuidGenerator $idGenerator)
    {
        $this->httpClient = $client;
        $this->idGenerator = $idGenerator;
    }

    public function sendReport($report)
    {
        $this->httpClient->post('https://telemetry.stillat.com/', [
            'app' => 'Meerkat-' . $this->idGenerator->newId(),
            'report' => $report
        ]);
    }

}