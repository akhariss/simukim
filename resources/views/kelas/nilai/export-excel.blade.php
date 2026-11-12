<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body>
<table border="1">
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Kelas</th>
        <th>Tipe</th>
        <th>Nilai Angka</th>
        <th>Nilai Huruf</th>
        <th>Catatan</th>
        <th>Diinput Oleh</th>
    </tr>
    @foreach($data as $i => $penilaian)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $penilaian->santri?->user?->name ?? '-' }}</td>
            <td>{{ $penilaian->santri?->nomor_induk ?? '-' }}</td>
            <td>{{ $penilaian->kelas?->nama ?? '-' }}</td>
            <td>{{ $penilaian->kelas?->tipe?->shortLabel() ?? '-' }}</td>
            <td>{{ $penilaian->nilai_angka !== null ? number_format((float)$penilaian->nilai_angka, 1) : '' }}</td>
            <td>{{ $penilaian->nilai_huruf ?? '' }}</td>
            <td>{{ $penilaian->catatan ?? '' }}</td>
            <td>{{ $penilaian->inputBy?->name ?? '-' }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
