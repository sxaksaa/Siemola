@props(['active'])

@php
    $classes = ($active ?? false)
            ? 'siemola-breeze-responsive-link siemola-breeze-responsive-link-active'
            : 'siemola-breeze-responsive-link siemola-breeze-responsive-link-idle';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
