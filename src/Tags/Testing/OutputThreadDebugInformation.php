<?php

namespace Stillat\Meerkat\Tags\Testing;

use Statamic\Statamic;
use Statamic\View\View;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\GetsHiddenContext;
use Stillat\Meerkat\Tags\MeerkatTag;

/**
 * Class OutputThreadDebugInformation
 *
 * Provides utilities to gather and render theme diagnostics data.
 *
 * @ls noparse
 *
 * @since 2.0.0
 */
class OutputThreadDebugInformation extends MeerkatTag
{
    use GetsHiddenContext;

    /**
     * Renders the Debug information tag.
     *
     * @return View|string
     */
    public function render()
    {
        $isSharingContext = $this->isSharingContext();
        $logicalContextId = $this->getCurrentContextId();
        $effectiveContextId = $this->getHiddenContext();

        if ($isSharingContext === true) {
            $isSharingContext = 'Yes';
        } else {
            $isSharingContext = 'No';
        }

        $report[] = $this->makeReportItem('Is Sharing Context?', $isSharingContext);
        $report[] = $this->makeReportItem('Logical Context', $logicalContextId);
        $report[] = $this->makeReportItem('Effective Context', $effectiveContextId);

        return view('meerkat::tags.debug.thread', [
            'statamicVersion' => Statamic::version(),
            'version' => Addon::VERSION,
            'report' => $report,
        ]);
    }

    /**
     * Generates a standardized debug report item.
     *
     * @param  string  $displayHeader The header to display to the user.
     * @param  string  $value The value to display to the user.
     * @return array
     */
    private function makeReportItem($displayHeader, $value)
    {
        return [
            'header' => $displayHeader,
            'value' => $value,
        ];
    }
}
