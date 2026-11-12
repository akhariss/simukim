<thead>
    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 text-[10.5px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
        <th class="px-4 py-3.5 w-12 text-center">No</th>

        @if($currentRole === 'mahasantri')
            <th class="px-4 py-3.5">Mahasantri</th>
            <th class="px-4 py-3.5">NIM & Kelamin</th>
            <th class="px-4 py-3.5">Kamar Asrama</th>
            <th class="px-4 py-3.5">Program Studi</th>
            <th class="px-4 py-3.5 text-center">Status Muqim</th>
            <th class="px-4 py-3.5 text-right">Aksi</th>
        @elseif($currentRole === 'mudabbir')
            <th class="px-4 py-3.5">Mudabbir Pembina</th>
            <th class="px-4 py-3.5">NIM & Kelamin</th>
            <th class="px-4 py-3.5">Kamar yang Ditempati</th>
            <th class="px-4 py-3.5">Program Studi</th>
            <th class="px-4 py-3.5">Kontak WhatsApp</th>
            <th class="px-4 py-3.5 text-right">Aksi</th>
        @elseif($currentRole === 'tutor')
            <th class="px-4 py-3.5">Ustadz / Tutor Pengajar</th>
            <th class="px-4 py-3.5">NIP / NIM Identitas</th>
            <th class="px-4 py-3.5">Asal Pengajar</th>
            <th class="px-4 py-3.5">Kelas Halaqah Dibina</th>
            <th class="px-4 py-3.5">Kontak WhatsApp</th>
            <th class="px-4 py-3.5 text-right">Aksi</th>
        @elseif($currentRole === 'admin')
            <th class="px-4 py-3.5">Pengelola Sistem</th>
            <th class="px-4 py-3.5">Nomor Induk / ID</th>
            <th class="px-4 py-3.5">Hak Akses</th>
            <th class="px-4 py-3.5">Terdaftar Sejak</th>
            <th class="px-4 py-3.5 text-right">Aksi</th>
        @else
            <th class="px-4 py-3.5">Pengguna & Akun</th>
            <th class="px-4 py-3.5">Nomor Induk / Identitas</th>
            <th class="px-4 py-3.5">Peran / Role</th>
            <th class="px-4 py-3.5">Kamar / Kelas Binaan</th>
            <th class="px-4 py-3.5 text-center">Status Santri</th>
            <th class="px-4 py-3.5 text-right">Aksi</th>
        @endif
    </tr>
</thead>
