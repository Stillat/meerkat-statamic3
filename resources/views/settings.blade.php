@extends('statamic::layout')
@section('title', trans('meerkat::display.header_configure'))
@section('wrapper_class', 'max-w-full')

@section('content')
    @if($user->can('super'))
        @include('statamic::partials.breadcrumb', [
                'url' => $addonUrl,
                'title' => trans('meerkat::general.statamic_addons'),
            ])
    @else
        @include('statamic::partials.breadcrumb', [
                'url' => $commentsUrl,
                'title' => trans('meerkat::display.header_comments'),
            ])
    @endif


    <meerkat-configurator></meerkat-configurator>
@stop
