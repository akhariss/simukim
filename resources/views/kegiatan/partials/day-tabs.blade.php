<!-- Quick Day Filter Tabs (Pill Navigation) -->
<div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
    <button
        type="button"
        @click="activeDayTab = 'all'"
        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer"
        :class="activeDayTab === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800'"
    >
        <span>Semua Hari</span>
    </button>
    @foreach($dayList as $dName)
        @php $countD = $scheduleByDay[$dName]->count(); @endphp
        <button
            type="button"
            @click="activeDayTab = '{{ strtolower($dName) }}'"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer"
            :class="activeDayTab === '{{ strtolower($dName) }}' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800'"
        >
            <span>{{ $dName }}</span>
            @if($countD > 0)
                <span
                    class="size-4 rounded-full flex items-center justify-center text-[10px] font-mono"
                    :class="activeDayTab === '{{ strtolower($dName) }}' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                >
                    {{ $countD }}
                </span>
            @endif
        </button>
    @endforeach
    @if($khususList->count() > 0)
        <button
            type="button"
            @click="activeDayTab = 'khusus'"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer"
            :class="activeDayTab === 'khusus' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-slate-800 hover:bg-purple-50 dark:hover:bg-slate-800'"
        >
            <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-purple-500"></span><span>Agenda Khusus</span></span>
            <span class="size-4 rounded-full flex items-center justify-center text-[10px] font-mono bg-purple-100 dark:bg-slate-800 text-purple-700 dark:text-purple-300">
                {{ $khususList->count() }}
            </span>
        </button>
    @endif
</div>
