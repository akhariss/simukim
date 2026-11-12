@props([
    'variant' => 'neutral',
    'dot' => false,
    'size' => 'xs',
])

@php
// Handle BackedEnum or Enum objects
if ($variant instanceof \BackedEnum) {
    $variant = method_exists($variant, 'variant') ? $variant->variant() : strtolower($variant->value);
} elseif (is_string($variant)) {
    $variant = strtolower($variant);
}

// Normalize common alias terms to atomic theme tokens
$variantMap = [
    'tersedia' => 'success',
    'aktif' => 'success',
    'disetujui' => 'success',
    'selesai' => 'success',
    'hadir' => 'success',
    'tahsin' => 'success',
    'mingguan' => 'success',

    'perbaikan' => 'warning',
    'pending' => 'warning',
    'dilaporkan' => 'warning',
    'pindah' => 'warning',
    'sakit' => 'warning',
    'sedang' => 'warning',
    'bulanan' => 'warning',
    'bahasainggris' => 'warning',

    'penuh' => 'danger',
    'ditolak' => 'danger',
    'alpa' => 'danger',
    'berat' => 'danger',
    'dikeluarkan' => 'danger',

    'diproses' => 'info',
    'izin' => 'info',
    'ringan' => 'info',
    'alumni' => 'info',
    'tahfidz' => 'info',
    'harian' => 'info',

    'nonaktif' => 'neutral',
    'keluar' => 'neutral',
    'dibatalkan' => 'neutral',

    'l' => 'ikhwan',
    'p' => 'akhwat',
];

$key = $variantMap[$variant] ?? $variant;

$colorClasses = [
    'success'  => 'badge-success',
    'warning'  => 'badge-warning',
    'danger'   => 'badge-danger',
    'info'     => 'badge-info',
    'neutral'  => 'badge-neutral',
    'mudabbir' => 'badge-mudabbir',
    'tutor'    => 'badge-tutor',
    'ikhwan'   => 'badge-ikhwan',
    'akhwat'   => 'badge-akhwat',
];

$dotColors = [
    'success'  => 'bg-emerald-500',
    'warning'  => 'bg-amber-500',
    'danger'   => 'bg-rose-500',
    'info'     => 'bg-blue-500',
    'neutral'  => 'bg-slate-400 dark:bg-slate-500',
    'mudabbir' => 'bg-amber-500',
    'tutor'    => 'bg-indigo-500',
    'ikhwan'   => 'bg-blue-500',
    'akhwat'   => 'bg-pink-500',
];

$sizeClasses = [
    'xs' => 'text-[10px] sm:text-[11px] px-2 py-0.5',
    'sm' => 'text-xs px-2.5 py-0.5',
    'md' => 'text-xs px-3 py-1',
];

$classes = ($colorClasses[$key] ?? $colorClasses['neutral']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['xs']);
$dotClass = $dotColors[$key] ?? $dotColors['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full font-semibold border shadow-2xs ' . $classes]) }}>
    @if($dot)
        <span class="size-1.5 rounded-full shrink-0 {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
