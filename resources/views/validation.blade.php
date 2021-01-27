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