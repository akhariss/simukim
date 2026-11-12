@props([
    'status',
    'dot' => false,
    'size' => 'xs',
])

@php
$label = $status instanceof \BackedEnum && method_exists($status, 'label')
    ? $status->label()
    : ucfirst((string)($status->value ?? $status));

$variant = $status instanceof \BackedEnum
    ? (method_exists($status, 'variant') ? $status->variant() : $status->value)
    : (string)$status;
@endphp

<x-badge :variant="$variant" :dot="$dot" :size="$size" {{ $attributes }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</x-badge>
