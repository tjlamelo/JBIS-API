@extends('pdf.layout')

@section('title', ($meta['title'] ?? $definition->fileName) . ' — JBIS')

@push('head')
    @include('pdf.partials.word-export-styles')
@endpush

@section('content')
    @include('pdf.partials.export-candidate-dossier')
@endsection
