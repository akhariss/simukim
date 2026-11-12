<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        h2 { font-size: 11px; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e40af; color: white; padding: 6px 8px; text-align: left; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .a-grade { color: #059669; font-weight: bold; }
        .b-grade { color: #2563eb; font-weight: bold; }
        .c-grade { color: #d97706; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Rekap Nilai — {{ $periode?->nama ?? '-' }}</h1>
    <h2>Dicetak: {{ now()->format('d M Y H:i') }}</h2>

    <table>
        <tr>
            <th>#</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Kelas</th>
            <th>Tipe</th>
            <th class="text-center">Nilai Angka</th>
            <th class="text-center">Nilai Huruf</th>
            <th>Catatan</th>
        </tr>
        @foreach($data as $i => $penilaian)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $penilaian->santri?->user?->name ?? '-' }}</td>
                <td>{{ $penilaian->santri?->nomor_induk ?? '-' }}</td>
                <td>{{ $penilaian->kelas?->nama ?? '-' }}</td>
                <td>{{ $penilaian->kelas?->tipe?->shortLabel() ?? '-' }}</td>
                <td class="text-center">{{ $penilaian->nilai_angka !== null ? number_format((float)$penilaian->nilai_angka, 1) : '-' }}</td>
                <td class="text-center">
                    @if($penilaian->nilai_huruf)
                        @php
                            $c = match(true) {
                                in_array($penilaian->nilai_huruf, ['A','A-']) => 'a-grade',
                                in_array($penilaian->nilai_huruf, ['B+','B','B-']) => 'b-grade',
                                in_array($penilaian->nilai_huruf, ['C+','C','C-']) => 'c-grade',
                                default => 'fail',
                            };
                        @endphp
                        <span class="{{ $c }}">{{ $penilaian->nilai_huruf }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $penilaian->catatan ?? '-' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
