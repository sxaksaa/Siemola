@props(['active'])

@php
    $classes = ($active ?? false)
            ? 'siemola-breeze-nav-link siemola-breeze-nav-link-active'
            : 'siemola-breeze-nav-link siemola-breeze-nav-link-idle';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
