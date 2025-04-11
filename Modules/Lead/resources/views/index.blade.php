@extends('lead::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('lead.name') !!}</p>
@endsection
