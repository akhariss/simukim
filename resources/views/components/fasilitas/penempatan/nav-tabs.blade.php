@props(['activeTab' => 'aktif', 'counts' => []])

@php
    $tabs = [
        [
            'id' => 'aktif',
            'label' => 'Mahasantri Mukim',
            'short_label' => 'Mukim',
            'count' => $counts['aktif'] ?? 0,
            'url' => route('fasilitas.penempatan.index', ['tab' => 'aktif']),
        ],
        [
            'id' => 'unassigned',
            'label' => 'Belum Ditempatkan',
            'short_label' => 'Antrean',
            'count' => $counts['unassigned'] ?? 0,
            'url' => route('fasilitas.penempatan.index', ['tab' => 'unassigned']),
        ],
        [
            'id' => 'riwayat',
            'label' => 'Riwayat Penempatan',
            'short_label' => 'Riwayat',
            'count' => $counts['riwayat'] ?? 0,
            'url' => route('fasilitas.penempatan.index', ['tab' => 'riwayat']),
        ],
    ];
@endphp

<x-tab-switcher
    :tabs="$tabs"
    :active="$activeTab"
    color="blue"
/>
