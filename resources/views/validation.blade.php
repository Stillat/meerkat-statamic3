@extends('statamic::layout')
@section('title', trans('meerkat::general.installation_validation'))

@section('content')
    <div class="flex items-center justify-between">
        <h1>{{ trans('meerkat::general.installation_validation') }}</h1>
    </div>

    <h2 class="mt-4 mb-1 font-bold text-lg">{{ trans('meerkat::validation.system_information') }}</h2>
    <div class="card p-2 mt-2">
        <table class="data-table">
            <tbody>
            @foreach($system_information as $value)
                <tr>
                    <th>{{ $value['name'] }}</th>
                    <td>{{ $value['value'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mb-1">
        <div class="flex items-center justify-between">
            <h2 class="mt-4 mb-1 font-bold text-lg">{{ trans('meerkat::validation.category_routes') }}</h2>

            @if($can_clear_route_cache)
            <form method="POST" action="{{ cp_route('utilities.meerkat-validation.meerkat.routes.clear.cache') }}">
                @csrf
                <button class="btn-primary mt-3">{{ trans('meerkat::validation.clear_route_cache')  }}</button>
            </form>
            @endif
        </div>
    </div>



    <div class="card p-2 mt-2">
        @if($has_route_issues === false)
            <p>{{ trans('meerkat::validation.routes_valid') }}</p>
        @else
            <p>{{ trans('meerkat::validation.routes_invalid') }}</p>

            <table class="data-table">
                <thead>
                <tr>
                    <th>{{ trans('meerkat::validation.route_table_header_name') }}</th>
                    <th>{{ trans('meerkat::validation.route_table_header_category') }}</th>
                    <th>{{ trans('meerkat::validation.route_table_header_description') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($missing_emissions as $emission)
                    <tr>
                        <th>{{ trans('meerkat::validation.emissions_'.$emission) }}</th>
                        <td>{{ trans('meerkat::validation.route_category_emissions') }}</td>
                        <td>{{ trans('meerkat::validation.emissions_'.$emission.'_desc') }}</td>
                    </tr>
                @endforeach

                @foreach($missing_categories as $category)
                    <tr>
                        <th>{{ trans('meerkat::validation.route_category_'.$category) }}</th>
                        <td>{{ trans('meerkat::validation.route_category_general') }}</td>
                        <td>{{ trans('meerkat::validation.route_category_'.$category.'_desc') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <h3 class="mt-3">{{ trans('meerkat::validation.route_clear_artisan_header') }}</h3>
        <p>{{ trans('meerkat::validation.route_clear_artisan_instructions', ['directory' => base_path()]) }}</p>
        <pre class="mt-1"><code>php artisan route:clear</code></pre>

        <h3 class="mt-3">{{ trans('meerkat::validation.route_clear_manual_header') }}</h3>
        <p>{{ trans('meerkat::validation.route_clear_manual_instructions') }}</p>
        <pre class="mt-1"><code>{{ app()->getCachedRoutesPath() }}</code></pre>
    </div>

    <h2 class="mt-4 mb-1 font-bold text-lg">{{ trans('meerkat::validation.category_config_dir') }}</h2>

    @foreach($config_directories as $entry)
        <div class="card p-2 mt-2">
            <h3 class="font-bold">{{ $entry['name'] }}</h3>
            <p>{{ $entry['description'] }}</p>

            <p class="mt-2"><strong>{{ trans('meerkat::validation.local_path') }}</strong><pre>{{ $entry['path'] }}</pre></p>

            <table class="data-table">
                <thead>
                <tr>
                    <th>{{ trans('meerkat::validation.does_exists') }}</th>
                    <th>{{ trans('meerkat::validation.is_writeable') }}</th>
                    <th>{{ trans('meerkat::validation.is_readable') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        @if ($entry['exists'] == true)
                            <span class="text-green">{{ trans('meerkat::validation.yes') }}</span>
                        @else
                            <span class="text-red"><strong>{{ trans('meerkat::validation.no') }}</strong></span>
                        @endif
                    </td>
                    <td>
                        @if ($entry['is_writeable'] == true)
                            <span class="text-green">{{ trans('meerkat::validation.yes') }}</span>
                        @else
                            <span class="text-red"><strong>{{ trans('meerkat::validation.no') }}</strong></span>
                        @endif
                    </td>
                    <td>
                        @if ($entry['is_readable'] == true)
                            <span class="text-green">{{ trans('meerkat::validation.yes') }}</span>
                        @else
                            <span class="text-red"><strong>{{ trans('meerkat::validation.no') }}</strong></span>
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="card p-0">
    </div>
@stop