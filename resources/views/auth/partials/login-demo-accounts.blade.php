<!-- Testing shortcut (Clean & Collapsible) -->
<div class="pt-1 text-center">
    <button
        type="button"
        @click="showDemoAccounts = !showDemoAccounts"
        class="text-[10px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 font-medium inline-flex items-center gap-1 transition cursor-pointer"
    >
        <span>Pintasan Akun Uji Coba</span>
        <svg class="size-3 transition-transform" :class="showDemoAccounts ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="showDemoAccounts" x-cloak class="mt-2.5 grid grid-cols-3 gap-1.5 text-left">
        <button
            type="button"
            @click="fastLogin('NIP-ADM01', 'password')"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:border-blue-500 transition cursor-pointer"
        >
            <span class="block text-[10px] font-bold text-slate-700 dark:text-slate-200">Admin</span>
            <span class="block text-[9px] text-slate-400 font-mono">ADM01</span>
        </button>
        <button
            type="button"
            @click="fastLogin('2388010004', 'password')"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:border-blue-500 transition cursor-pointer"
        >
            <span class="block text-[10px] font-bold text-slate-700 dark:text-slate-200">Santri 1</span>
            <span class="block text-[9px] text-slate-400 font-mono">...0004</span>
        </button>
        <button
            type="button"
            @click="fastLogin('2388010002', 'password')"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:border-blue-500 transition cursor-pointer"
        >
            <span class="block text-[10px] font-bold text-slate-700 dark:text-slate-200">Santri 2</span>
            <span class="block text-[9px] text-slate-400 font-mono">...0002</span>
        </button>
    </div>
</div>

