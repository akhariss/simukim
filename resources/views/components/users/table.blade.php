@php
    $currentRole = request('role');
@endphp

<!-- TABEL DATA PENGGUNA ADAPTIF STANDAR PERUSAHAAN (MODULAR) -->
<x-table-container>
    <!-- TAMPILAN DESKTOP & TABLET (md ke atas): TABEL LENGKAP -->
    <x-slot:desktop>
        @include('components.users.table-desktop')
    </x-slot:desktop>

    <!-- TAMPILAN KHUSUS MOBILE (< md): STACKED CARDS (HARAM HORIZONTAL SCROLL) -->
    <x-slot:mobile>
        @include('components.users.table-mobile')
    </x-slot:mobile>

    <!-- Paginasi Data Pengguna -->
    <x-slot:pagination>
        {{ $users->links() }}
    </x-slot:pagination>
</x-table-container>
