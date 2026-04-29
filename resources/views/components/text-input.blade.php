@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'siemola-breeze-input']) }}>
