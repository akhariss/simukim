<!-- MODE 2: TABEL FLAT KAMAR (Atomic Table Container - Desktop Table vs Mobile Stacked Cards) -->
<x-table-container>
    <x-slot:desktop>
        @include('components.fasilitas.kelola.kamar-table-flat-desktop')
    </x-slot:desktop>

    <x-slot:mobile>
        @include('components.fasilitas.kelola.kamar-table-flat-mobile')
    </x-slot:mobile>

    <x-slot:pagination>
        {{ $kamarList->links() }}
    </x-slot:pagination>
</x-table-container>
