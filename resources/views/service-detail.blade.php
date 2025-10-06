@extends('layouts.public')

@section('title', $page_title)
@section('description', $meta_description)

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-4">{{ $service['title'] }}</h1>
    <p class="text-gray-600 mb-6">{{ $service['meta_description'] }}</p>

    <div class="prose">
        {!! $service['content'] ?? 'More details about this service will be available soon.' !!}
    </div>
</div>
@endsection

