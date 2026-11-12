@props(['gender'])

@php
$g = $gender instanceof \App\Enums\Gender ? $gender->value : $gender;
$isL = strtoupper((string)$g) === 'L';
@endphp

<x-badge :variant="$isL ? 'ikhwan' : 'akhwat'" {{ $attributes }}>
    {{ $isL ? 'Ikhwan' : 'Akhwat' }}
</x-badge>
