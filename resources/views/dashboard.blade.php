@extends('statamic::layout')
@section('title', trans('meerkat::display.header_comments'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <meerkat-comment-thread></meerkat-comment-thread>
@stop
