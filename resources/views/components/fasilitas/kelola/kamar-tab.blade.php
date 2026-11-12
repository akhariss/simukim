@props([
    'kamarList' => null,
    'gedungList' => null,
    'allGedung' => null,
    'roomStats' => null,
    'stats' => null,
    'totalPenghuni' => 0,
    'viewMode' => 'per_lantai',
])

@php
    $viewMode = request('view', $viewMode ?? 'per_lantai');
    $isTable = in_array($viewMode, ['table', 'daftar']);
@endphp

<!-- Tab Kelola Kamar Asrama (Orchestrator Modular) -->
<div class="space-y-4">
    <!-- 1. Ringkasan Eksekutif Kapasitas & Hunian Kamar -->
    @include('components.fasilitas.kelola.kamar-stats')

    <!-- 2. Toolbar Filter & Aksi Cepat -->
    @include('components.fasilitas.kelola.kamar-filters')

    <!-- 3. Tampilan Data: Grup Lantai vs Tabel Flat -->
    @if($isTable)
        @include('components.fasilitas.kelola.kamar-table-flat')
    @else
        @include('components.fasilitas.kelola.kamar-grid-lantai')
    @endif
</div>
