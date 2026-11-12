<!-- Master Cache Keys Registry Table -->
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs overflow-hidden">
    <div class="px-4 py-3 sm:px-5 sm:py-3.5 border-b border-slate-200/80 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-between">
        <div>
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                Kunci Cache Terdaftar & Masa Aktif (TTL)
            </h3>
            <p class="text-[11px] text-slate-400">Daftar kunci memori yang dikelola otomatis oleh arsitektur aplikasi</p>
        </div>
    </div>

    <!-- Desktop View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-slate-500 font-semibold border-b border-slate-200/80 dark:border-slate-800/80 uppercase text-[10.5px]">
                <tr>
                    <th class="px-4 py-3">Nama Key</th>
                    <th class="px-4 py-3">Deskripsi & Fungsi</th>
                    <th class="px-4 py-3">TTL</th>
                    <th class="px-4 py-3">Status Saat Ini</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                @foreach($registeredKeys as $keyName => $meta)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-blue-600 dark:text-blue-400">
                            {{ $keyName }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $meta['label'] }}</div>
                            <div class="text-[11px] text-slate-400">{{ $meta['desc'] }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-500">
                            {{ $meta['ttl'] }}
                        </td>
                        <td class="px-4 py-3">
                            @if($meta['exists'])
                                <x-badge variant="success" dot size="xs">
                                    Tersimpan
                                </x-badge>
                            @else
                                <x-badge variant="neutral" size="xs">
                                    Kosong / On-Demand
                                </x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.cache.clear') }}" class="inline" data-confirm="Hapus kunci cache '{{ $keyName }}'?">
                                @csrf
                                <input type="hidden" name="type" value="key">
                                <input type="hidden" name="key" value="{{ $keyName }}">
                                <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg text-slate-600 hover:text-rose-600 hover:bg-rose-50 dark:text-slate-400 dark:hover:text-rose-400 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                                    Invalidate
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile View (Stacked Cards) -->
    <div class="block md:hidden divide-y divide-slate-200/60 dark:divide-slate-800/60">
        @foreach($registeredKeys as $keyName => $meta)
            <div class="p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400">{{ $keyName }}</span>
                    @if($meta['exists'])
                        <x-badge variant="success" size="xs">Tersimpan</x-badge>
                    @else
                        <x-badge variant="neutral" size="xs">On-Demand</x-badge>
                    @endif
                </div>
                <div class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ $meta['label'] }}</div>
                <div class="text-[11px] text-slate-400">{{ $meta['desc'] }} (TTL: {{ $meta['ttl'] }})</div>
                <div class="pt-1">
                    <form method="POST" action="{{ route('admin.cache.clear') }}" data-confirm="Hapus kunci cache '{{ $keyName }}'?">
                        @csrf
                        <input type="hidden" name="type" value="key">
                        <input type="hidden" name="key" value="{{ $keyName }}">
                        <button type="submit" class="w-full py-1.5 text-xs font-semibold rounded-lg text-rose-600 bg-rose-50 dark:bg-slate-800 border border-rose-200 dark:border-slate-700 transition">
                            Invalidate Key
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
