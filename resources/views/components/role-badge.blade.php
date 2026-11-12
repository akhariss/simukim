@props(['role'])

@php
$r = strtolower((string)$role);
$variant = match($r) {
    'mudabbir' => 'mudabbir',
    'tutor' => 'tutor',
    'admin' => 'info',
    default => 'neutral',
};
$label = match($r) {
    'mudabbir' => 'Mudabbir',
    'tutor' => 'Tutor',
    'admin' => 'Admin',
    default => ucfirst($r),
};
@endphp

<x-badge :variant="$variant" {{ $attributes }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</x-badge>
