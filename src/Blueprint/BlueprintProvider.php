<?php

namespace Stillat\Meerkat\Blueprint;

use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Yaml\ParseException;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Parsing\YAMLParser;
use Stillat\Meerkat\PathProvider;

/**
 * Class BlueprintProvider
 *
 * Ensures that the default Meerkat blueprint is available to the Statamic installation.
 *
 * @since 2.0.0
 */
class BlueprintProvider
{
    /**
     * The blueprint repository implementation instance.
     *
     * @var BlueprintRepository|null
     */
    protected $blueprints = null;

    /**
     * The YAML parser instance.
     *
     * @var YAMLParser|null
     */
    protected $yamlParser = null;

    public function __construct(BlueprintRepository $blueprintRepository, YAMLParser $yamlParser)
    {
        $this->blueprints = $blueprintRepository;
        $this->yamlParser = $yamlParser;
    }

    /**
     * Gets the Meerkat blueprint.
     *
     * @return Blueprint
     *
     * @throws ParseException
     */
    public function getBlueprint()
    {
        $this->ensureExistence();

        return $this->blueprints->find(Addon::CODE_ADDON_NAME);
    }

    /**
     * Ensures that the default Meerkat blueprint is available.
     *
     * @return void
     *
     * @throws ParseException
     */
    public function ensureExistence()
    {
        if ($this->hasDefaultBlueprint() === false) {
            $blueprint = $this->makeBlueprint();

            $blueprint->save();
        }
    }

    /**
     * Determines if the Statamic installation has the required blueprint.
     *
     * @return bool
     */
    public function hasDefaultBlueprint()
    {
        $bluePrint = $this->blueprints->find(Addon::CODE_ADDON_NAME);

        if ($bluePrint === null) {
            return false;
        }

        return true;
    }

    /**
     * Creates a default blueprint.
     *
     * @return Blueprint
     *
     * @throws ParseException
     */
    private function makeBlueprint()
    {
        $blueprintStub = file_get_contents(PathProvider::getStub('blueprint.yaml'));
        $sections = $this->yamlParser->parseDocument($blueprintStub);

        $blueprint = $this->blueprints->make(Addon::CODE_ADDON_NAME);
        $blueprint->setContents($sections);

        return $blueprint;
    }
}
