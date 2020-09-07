<?php

namespace Stillat\Meerkat\Support\Facades\StaticHelpers;

use Illuminate\Support\Facades\Event;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Providers\ControlPanelServiceProvider;

trait ProvidesEventHelpers
{


    public static function onRegisteringControlPanel(callable $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . ControlPanelServiceProvider::EVENT_REGISTERING_CONTROL_PANEL, $handler);
    }

    public static function onCollectingComment(callable $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . CommentMutationPipelineContract::MUTATION_COLLECTION, $handler);
    }

    public static function onCollectingAllComments(callable $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . CommentMutationPipelineContract::MUTATION_COLLECTION_ALL, $handler);
    }

}
