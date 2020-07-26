@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('cp.meerkat.dashboard'),
        'title' => trans('meerkat::general.meerkat'),
    ])

    <blueprint-builder
            action="{{ cp_route('cp.meerkat.blueprint.update') }}"
            :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
