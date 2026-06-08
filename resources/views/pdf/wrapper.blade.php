@extends('pdf.layout')

@section('title', $title ?? config('app.name', 'JBIS'))

@section('content')
    {!! $bodyHtml !!}
@endsection
