<?php

namespace Stillat\Meerkat\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;
use Stillat\Meerkat\Blueprint\BlueprintProvider;

/**
 * Class MeerkatBlueprintController
 *
 * Contains features and resources for interacting with the Meerkat blueprint.
 *
 * @since 2.0.0
 */
class MeerkatBlueprintController extends CpController
{
    use ManagesBlueprints;

    /**
     * @var BlueprintProvider|null
     */
    private $blueprintProvider = null;

    public function __construct(Request $request, BlueprintProvider $blueprintProvider)
    {
        parent::__construct($request);
        $this->blueprintProvider = $blueprintProvider;
    }

    public function edit()
    {
        $blueprint = $this->blueprintProvider->getBlueprint();

        return view('meerkat::blueprints.edit', [
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'sections' => 'array',
        ]);

        $this->updateBlueprint($request, $this->blueprintProvider->getBlueprint());
    }
}
