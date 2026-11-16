{{-- Tabel Santri Belum Ditempatkan --}}
<x-table-container>
    <x-slot:toolbar>
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <svg class="size-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="text-xs font-bold text-amber-900 dark:text-amber-300">
                    Antrean Menunggu Penempatan — {{ $unassignedSantri->total() }} Mahasantri
                </span>
            </div>
            <span class="text-[11px] text-amber-700 dark:text-amber-400 font-medium">Siap ditempatkan</span>
        </div>
    </x-slot:toolbar>

    <x-slot:desktop>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-700/80">
                    <th class="px-5 py-3 w-12 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">No</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Mahasantri</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Gender</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unassignedSantri as $santri)
                    @php $isMudabbir = $santri->status === 'mudabbir'; @endphp
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-center text-slate-400 dark:text-slate-500 font-mono text-xs font-semibold">
                            {{ $unassignedSantri->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">
                                    {{ $santri->user->name ?? '-' }}
                                </span>
                                @if($isMudabbir)
                                    <x-role-badge role="mudabbir" />
                                @endif
                            </div>
                            <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">{{ $santri->nomor_induk }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span>{{ $santri->prodi ?? 'Reguler' }}{{ !empty($santri->angkatan) ? " '" . substr((string)$santri->angkatan, -2) : '' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <x-gender-badge :gender="$santri->gender" />
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <button
                                type="button"
                                @click="$dispatch('open-assign-modal', {
                                    santriId: {{ $santri->id }},
                                    santriName: @js($santri->user->name ?? ''),
                                    gender: @js((string) $santri->gender)
                                })"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition cursor-pointer"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tempatkan</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-400">
                            Alhamdulillah, seluruh mahasantri aktif telah mendapatkan kamar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse($unassignedSantri as $santri)
            @php $isMudabbir = $santri->status === 'mudabbir'; @endphp
            <div class="p-3.5 space-y-2">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-mono text-[10px] text-slate-400 font-bold">
                                #{{ $unassignedSantri->firstItem() + $loop->index }}
                            </span>
                            <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white truncate">
                                {{ $santri->user->name ?? '-' }}
                            </span>
                            @if($isMudabbir)
                                <x-role-badge role="mudabbir" />
                            @endif
                        </div>
                        <p class="mt-0.5 text-[10.5px] text-slate-500 dark:text-slate-400">
                            <span class="font-mono font-semibold">{{ $santri->nomor_induk }}</span>
                            · {{ $santri->prodi ?? 'Reguler' }}
                        </p>
                    </div>
                    <x-gender-badge :gender="$santri->gender" />
                </div>

                <div class="flex items-center justify-end pt-1 border-t border-slate-100/80 dark:border-slate-800/80">
                    <button
                        type="button"
                        @click="$dispatch('open-assign-modal', {
                            santriId: {{ $santri->id }},
                            santriName: @js($santri->user->name ?? ''),
                            gender: @js((string) $santri->gender)
                        })"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-2xs cursor-pointer"
                    >
                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tempatkan</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-slate-400">
                Alhamdulillah, seluruh mahasantri aktif telah mendapatkan kamar.
            </div>
        @endforelse
    </x-slot:mobile>

    @if($unassignedSantri->hasPages())
        <x-slot:pagination>
            {{ $unassignedSantri->links() }}
        </x-slot:pagination>
    @endif
</x-table-container>
