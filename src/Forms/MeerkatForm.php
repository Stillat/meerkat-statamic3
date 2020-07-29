<?php

namespace Stillat\Meerkat\Forms;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DebugBarException;
use Illuminate\Support\MessageBag;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Field;
use Statamic\Tags\Concerns\GetsFormSession;
use Statamic\Tags\Concerns\GetsRedirects;
use Statamic\Tags\Concerns\RendersForms;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\GetsHiddenContext;
use Stillat\Meerkat\Core\Http\Client;
use Stillat\Meerkat\Tags\MeerkatTag;

/**
 * Class MeerkatForm
 *
 *
 *
 * @package Stillat\Meerkat\Forms
 * @since 2.0.0
 */
class MeerkatForm extends MeerkatTag
{
    use RendersForms, GetsFormSession, GetsRedirects, GetsHiddenContext;

    const KEY_MEERKAT_FORM = '_meerkat_form';
    const KEY_PARAM_ERROR_REDIRECT = '_error_redirect';
    const KEY_MEERKAT_CONTEXT = '_meerkat_context';
    const KEY_DATA_FIELDS = 'fields';
    const KEY_FORM_CONFIG_VALIDATE = 'validate';
    const KEY_FORM_CONFIG_DISPLAY_NAME = 'display';

    /**
     * The blueprint repository implementation.
     *
     * @var BlueprintRepository|null
     */
    protected $blueprints = null;

    const HANDLE_PARAM = ['blueprint'];

    protected $hasValuesHandled = [
        MeerkatForm::KEY_MEERKAT_FORM,
        MeerkatForm::KEY_MEERKAT_CONTEXT
    ];

    /**
     * The blueprint instance.
     *
     * @var Blueprint|null
     */
    protected $blueprint = null;

    protected $blueprintName = '';

    public function __construct(BlueprintRepository $blueprints)
    {
        $this->blueprints = $blueprints;
    }

    public function render()
    {
        $bluePrint = $this->getParam('blueprint', Addon::CODE_ADDON_NAME);
        $this->blueprintName = $bluePrint;
        $sessionHandle = self::getFormSessionHandle($bluePrint);

        $data = $this->getFormSession($sessionHandle);
        $data[self::KEY_DATA_FIELDS] = $this->getFields($sessionHandle);

        $this->addToDebugBar($bluePrint, $data);

        $knownParams = array_merge(static::HANDLE_PARAM, ['redirect', 'error_redirect', 'allow_request_redirect']);

        $html = $this->formOpen('/!/Meerkat/socialize', Client::HTTP_POST, $knownParams);

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Gets a form session handle for the provided blueprint name.
     *
     * @param string $blueprintName The name of the blueprint.
     * @return string
     */
    public static function getFormSessionHandle($blueprintName)
    {
        return 'meerkat.form'.$blueprintName;
    }

    private function makeHiddenField($name, $value)
    {
        $hiddenField = new Field($name, [
            'type' => 'hidden',
            'validate' => 'required'
        ]);

        $hiddenField->setValue($value);

        return $hiddenField;
    }

    private function getContextualFields()
    {
        $meerkatBlueprint = $this->makeHiddenField('_meerkat_form', $this->blueprintName);
        $context = $this->makeHiddenField('_meerkat_context', $this->getHiddenContext());

        return [
            $meerkatBlueprint,
            $context
        ];
    }

    protected function getRenderableField($field, $errorBag = 'default')
    {
        $errors = session('errors') ? session('errors')->getBag($errorBag) : new MessageBag;

        $data = array_merge($field->toArray(), [
            'error' => $errors->first($field->handle()) ?: null,
            'old' => old($field->handle()),
        ]);

        if ($data['type'] === 'hidden') {
            if (in_array($data['handle'], $this->hasValuesHandled)) {
                $data['value'] = $field->value();
                $data['field'] = view('meerkat::form.fields.hidden_value', $data);
            } else {
                $data['field'] = view('meerkat::form.fields.hidden', $data);
            }
        } else {
            $data['field'] = view($field->fieldtype()->view(), $data);
        }

        return $data;
    }

    /**
     * Gets fields with extra data for looping over and rendering.
     *
     * @param string $sessionHandle The form's session handle.
     * @return array
     */
    private function getFields($sessionHandle)
    {
        $fields = $this->getBlueprint()->fields()->all()->merge($this->getContextualFields())
            ->map(function ($field) use ($sessionHandle) {
                return $this->getRenderableField($field, $sessionHandle);
            })
            ->values()
            ->all();

        return $fields;
    }

    /**
     * Gets the current blueprint instance, if any.
     *
     * @return Blueprint|null
     */
    private function getBlueprint()
    {
        if ($this->blueprint !== null) {
            return $this->blueprint;
        }

        $bluePrintHandle = $this->getParam('blueprint', 'meerkat');

        $this->blueprint = $this->blueprints->find($bluePrintHandle);

        return $this->blueprint;
    }

    protected function addToDebugBar($data, $formHandle)
    {
        if (!function_exists('debug_bar')) {
            return;
        }

        $debug = [];
        $debug[$formHandle] = $data;

        if ($this->blink->exists('debug_bar_data')) {
            $debug = array_merge($debug, $this->blink->get('debug_bar_data'));
        }

        $this->blink->put('debug_bar_data', $debug);

        try {
            debugbar()->getCollector('Forms')->setData($debug);
        } catch (DebugBarException $e) {
            // Collector doesn't exist yet. We'll create it.
            $collector = debugbar()->addCollector(new ConfigCollector($debug, 'Forms'));
        }
    }

}