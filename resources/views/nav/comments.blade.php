<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>@svg($item->icon())</i><span>{{ __($item->name()) }}</span>
        <meerkat-nav-badge class="ml-1"></meerkat-nav-badge>
    </a>
</li>
