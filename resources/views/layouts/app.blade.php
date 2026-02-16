@props(['header' => '', 'title' => ''])

@component('layouts.app.sidebar', ['header' => $header, 'title' => $title ?? null])
    {{ $slot }}
    
@endcomponent