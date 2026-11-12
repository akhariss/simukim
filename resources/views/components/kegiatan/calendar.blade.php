@props(['allEvents' => null, 'allKegiatan' => null, 'bulan', 'tahun'])

@php
    $events = $allEvents ?? $allKegiatan ?? collect();
    $currentMonthCarbon = \Carbon\Carbon::create($tahun, $bulan, 1);
    $daysInMonth = $currentMonthCarbon->daysInMonth;
    // Mulai Minggu (0) hingga Sabtu (6)
    $startDayOfWeek = $currentMonthCarbon->dayOfWeek;
    $prevMonth = $currentMonthCarbon->copy()->subMonth();
    $nextMonth = $currentMonthCarbon->copy()->addMonth();

    $dayNamesMap = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

    $matchesDay = fn($h, $t) => $h && (strtolower(trim($t)) === 'minggu' 
        ? (str_contains(strtolower($h), 'ahad') || str_contains(strtolower($h), 'minggu')) 
        : str_contains(strtolower($h), strtolower(trim($t))));

    $processEvents = function($rawList) {
        $res = collect(); $grup = [];
        foreach ($rawList as $item) {
            $isK = $item->is_kelas ?? false; $t = $item->tipe ?? '';
            ($isK && in_array($t, ['grup', 'mudabir'])) ? $grup[($item->jam_mulai ?? '00:00') . '_' . $t][] = $item : $res->push($item);
        }
        foreach ($grup as $timeKey => $items) {
            if (count($items) > 1) {
                $s = $items[0]; $lbl = $s->tipe === 'mudabir' ? 'Dauroh/Kelas Mudabbir' : 'Halaqah / Kelas Grup';
                $c = count($items);
                $res->push((object) [
                    'id' => 'grouped-' . $timeKey, 'nama' => "{$lbl} ({$c} Grup Serentak)", 'is_kelas' => true,
                    'is_grouped' => true, 'group_count' => $c, 'sub_items' => $items, 'tipe' => $s->tipe,
                    'tipe_label' => $lbl, 'tipe_short' => 'Grup', 'jenis_code' => 'kelas', 'jenis_label' => $lbl,
                    'hari' => $s->hari, 'jam_mulai' => $s->jam_mulai, 'jam_selesai' => $s->jam_selesai,
                    'lokasi' => "{$c} Kelompok Tersebar", 'tutor_name' => "{$c} Tutor Pembina",
                    'deskripsi' => "Sesi kelas paralel serentak {$c} kelompok.", 'url' => null,
                ]);
            } else { $res->push($items[0]); }
        }
        return $res->sortBy(fn($ev) => $ev->jam_mulai ?? '99:99')->values();
    };

    $now = \Carbon\Carbon::now();
    $isCurrentMonth = ($now->month == $bulan && $now->year == $tahun);
    $initialDay = $isCurrentMonth ? $now->day : 1;
    $initialDateCarbon = \Carbon\Carbon::create($tahun, $bulan, $initialDay);
    $initialDateTitle = $initialDateCarbon->translatedFormat('l, d F Y');
    $initialIsToday = $initialDateCarbon->isToday();

    $initialDayOfWeek = $dayNamesMap[$initialDateCarbon->dayOfWeek];
    $rawInitEvents = $events->filter(function($item) use ($initialDayOfWeek, $initialDateCarbon, $matchesDay) {
        $isKelas = $item->is_kelas ?? false;
        $jenisCode = $item->jenis_code ?? ($item->jenis instanceof \App\Enums\KegiatanJenis ? $item->jenis->value : $item->jenis ?? '');
        if ($isKelas || $jenisCode === 'rutin') return $matchesDay($item->hari, $initialDayOfWeek);
        if ($jenisCode === 'khusus' && $item->tanggal_mulai) {
            $start = \Carbon\Carbon::parse($item->tanggal_mulai)->startOfDay();
            $end = $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->endOfDay() : $start->copy()->endOfDay();
            return $initialDateCarbon->betweenIncluded($start, $end);
        }
        return false;
    })->values();

    $initialEvents = $processEvents($rawInitEvents);
@endphp

<div
    x-data="{
        selectedDay: {{ $initialDay }},
        selectedDateTitle: '{{ $initialDateTitle }}',
        selectedDayIsToday: {{ $initialIsToday ? 'true' : 'false' }},
        selectedEvents: {{ Js::from($initialEvents) }},
        selectDay(dayNum, title, isToday, events) {
            this.selectedDay = dayNum;
            this.selectedDateTitle = title;
            this.selectedDayIsToday = isToday;
            this.selectedEvents = events;
        }
    }"
    class="space-y-4"
>
    <!-- Calendar Grid Card Terpadu (Anti-Makan Tempat & Proporsional) -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 overflow-hidden shadow-2xs">
        <x-kegiatan.calendar-header :current-month-carbon="$currentMonthCarbon" :prev-month="$prevMonth" :next-month="$nextMonth" />

        <!-- Day Names Header (Minggu s/d Sabtu) -->
        <div class="grid grid-cols-7 border-b border-slate-200/80 dark:border-slate-800 text-center py-2.5 bg-slate-50/80 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase tracking-wider text-slate-500">
            <div class="text-rose-600 dark:text-rose-400">Min</div>
            <div>Sen</div>
            <div>Sel</div>
            <div>Rab</div>
            <div>Kam</div>
            <div>Jum</div>
            <div class="text-indigo-600 dark:text-indigo-400">Sab</div>
        </div>

        <!-- Calendar Days Matrix -->
        <div class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-800/60">
            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="min-h-[56px] sm:min-h-[105px] p-1 sm:p-1.5 bg-slate-50/30 dark:bg-slate-800/20 text-slate-300 dark:text-slate-700 text-xs"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $loopDate = \Carbon\Carbon::create($tahun, $bulan, $day);
                    $dayOfWeekName = $dayNamesMap[$loopDate->dayOfWeek];
                    $isToday = $loopDate->isToday();
                    $isSunday = ($loopDate->dayOfWeek === 0);
                    $isSaturday = ($loopDate->dayOfWeek === 6);

                    $rawEventsForDay = $events->filter(function($item) use ($dayOfWeekName, $loopDate, $matchesDay) {
                        $isKelas = $item->is_kelas ?? false;
                        $jenisCode = $item->jenis_code ?? ($item->jenis instanceof \App\Enums\KegiatanJenis ? $item->jenis->value : $item->jenis ?? '');
                        if ($isKelas || $jenisCode === 'rutin') return $matchesDay($item->hari, $dayOfWeekName);
                        if ($jenisCode === 'khusus' && $item->tanggal_mulai) {
                            $start = \Carbon\Carbon::parse($item->tanggal_mulai)->startOfDay();
                            $end = $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->endOfDay() : $start->copy()->endOfDay();
                            return $loopDate->betweenIncluded($start, $end);
                        }
                        return false;
                    })->values();

                    $eventsForDay = $processEvents($rawEventsForDay);
                    $dateTitleStr = $loopDate->translatedFormat('l, d F Y');
                @endphp
                <div
                    @click="selectDay({{ $day }}, '{{ $dateTitleStr }}', {{ $isToday ? 'true' : 'false' }}, {{ Js::from($eventsForDay) }})"
                    class="min-h-[56px] sm:min-h-[105px] p-1 sm:p-1.5 transition-all hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer flex flex-col justify-between group rounded-lg"
                    :class="selectedDay === {{ $day }} ? 'ring-2 ring-blue-600 dark:ring-blue-500 bg-blue-50/50 dark:bg-slate-800/80' : '{{ $isToday ? 'bg-blue-50/25 dark:bg-slate-800/40' : '' }}'"
                    title="Klik tanggal {{ $day }} untuk melihat agenda"
                >
                    <div class="flex items-center justify-center sm:justify-between w-full">
                        <span
                            class="size-5 sm:size-6 rounded-full flex items-center justify-center text-xs font-bold transition-all {{ $isSunday ? 'text-rose-600 dark:text-rose-400' : ($isSaturday ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300') }}"
                            :class="selectedDay === {{ $day }} ? '!bg-blue-600 !text-white shadow-xs' : '{{ $isToday ? '!bg-blue-100 !text-blue-700 dark:!bg-slate-800 dark:!text-blue-300' : '' }}'"
                        >
                            {{ $day }}
                        </span>
                        @if($eventsForDay->count() > 0)
                            <span class="text-[10px] font-mono text-slate-400 font-semibold hidden sm:inline">
                                {{ $eventsForDay->count() }}
                            </span>
                        @endif
                    </div>

                    {{-- MOBILE ONLY: Micro Dots Indikator (Anti-Kepotong di HP) --}}
                    @if($eventsForDay->count() > 0)
                        <div class="flex items-center justify-center gap-1 mt-1 sm:hidden">
                            @foreach($eventsForDay->take(3) as $ev)
                                <span class="size-1.5 rounded-full {{ $ev->is_kelas ? 'bg-emerald-500' : ($ev->jenis_code === 'rutin' ? 'bg-blue-500' : 'bg-purple-500') }}"></span>
                            @endforeach
                            @if($eventsForDay->count() > 3)
                                <span class="size-1 rounded-full bg-slate-400"></span>
                            @endif
                        </div>
                    @endif

                    {{-- DESKTOP ONLY: Event Pills dengan Info Tipe Kelas --}}
                    <div class="space-y-1 mt-1 flex-1 overflow-y-auto max-h-24 hidden sm:block">
                        @foreach($eventsForDay->take(3) as $ev)
                            @php
                                $isK = $ev->is_kelas ?? false;
                                $jCode = $ev->jenis_code ?? ($ev->jenis instanceof \App\Enums\KegiatanJenis ? $ev->jenis->value : $ev->jenis ?? '');
                                $jamStr = $ev->jam_mulai ? ($ev->jam_selesai ? "{$ev->jam_mulai}-{$ev->jam_selesai}" : $ev->jam_mulai) : '';
                            @endphp

                            @if($isK)
                                <div class="px-1.5 py-0.5 rounded-md text-[10px] leading-tight font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/60 truncate"
                                    title="{{ $ev->nama }} [{{ $ev->tipe_label ?? 'Kajian' }}] ({{ $jamStr }})">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="font-bold truncate">{{ $ev->nama }}</span>
                                        <span class="text-[8.5px] uppercase font-bold px-1 rounded bg-emerald-200/70 dark:bg-emerald-900/80 text-emerald-900 dark:text-emerald-200 shrink-0">
                                            {{ $ev->tipe_short ?? 'Kelas' }}
                                        </span>
                                    </div>
                                    @if($jamStr)
                                        <span class="block text-[9px] opacity-75 font-mono">{{ $jamStr }}</span>
                                    @endif
                                </div>
                            @elseif($jCode === 'rutin')
                                <div class="px-1.5 py-0.5 rounded-md text-[10px] leading-tight font-medium bg-blue-50 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800/60 truncate"
                                    title="[Rutin] {{ $ev->nama }} ({{ $jamStr }})">
                                    <span class="font-bold truncate block">{{ $ev->nama }}</span>
                                    @if($jamStr)
                                        <span class="block text-[9px] opacity-75 font-mono">{{ $jamStr }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="px-1.5 py-0.5 rounded-md text-[10px] leading-tight font-medium bg-purple-50 dark:bg-purple-950/50 text-purple-800 dark:text-purple-300 border border-purple-200/70 dark:border-purple-800/60 truncate"
                                    title="[Khusus] {{ $ev->nama }} ({{ $jamStr }})">
                                    <span class="font-bold truncate block">{{ $ev->nama }}</span>
                                    @if($jamStr)
                                        <span class="block text-[9px] opacity-75 font-mono">{{ $jamStr }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if($eventsForDay->count() > 3)
                            <div class="text-[9px] font-bold text-blue-600 dark:text-blue-400 pt-0.5 truncate">
                                +{{ $eventsForDay->count() - 3 }} lainnya...
                            </div>
                        @endif
                    </div>
                </div>
            @endfor

            @php
                $totalCells = $startDayOfWeek + $daysInMonth;
                $remainingCells = (7 - ($totalCells % 7)) % 7;
            @endphp
            @for($j = 0; $j < $remainingCells; $j++)
                <div class="min-h-[56px] sm:min-h-[105px] p-1 sm:p-1.5 bg-slate-50/30 dark:bg-slate-800/20 text-slate-300 dark:text-slate-700 text-xs"></div>
            @endfor
        </div>
    </div>

    <!-- Section Terpadu di Bawah Kalender: Detail Agenda Hari Terpilih -->
    @include('components.kegiatan.calendar-agenda')
</div>
