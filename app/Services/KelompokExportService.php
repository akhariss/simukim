<?php

namespace App\Services;

use App\Models\Kelompok;
use App\Models\Periode;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KelompokExportService
{
    /**
     * Export native OpenXML Excel (.xlsx) dengan Sheet Ringkasan Kelompok & Detail per-kelompok.
     */
    public function exportExcel(Collection $kelompokList, Periode $periode): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Ma'had Al-Jami'ah")
            ->setTitle("Daftar Kelompok Mahasantri — " . $periode->nama);

        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Ringkasan Kelompok');

        $summarySheet->mergeCells('A1:G1');
        $summarySheet->setCellValue('A1', "MA'HAD AL-JAMI'AH — REKAPITULASI KELOMPOK MAHASANTRI");
        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));

        $summarySheet->mergeCells('A2:G2');
        $summarySheet->setCellValue('A2', "Periode: " . $periode->nama . " (" . $periode->kode . ") | Dicetak: " . now()->format('d/m/Y H:i'));
        $summarySheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        $headers = ['A4' => 'No', 'B4' => 'Nama Kelompok', 'C4' => 'Kode', 'D4' => 'Kapasitas', 'E4' => 'Jumlah Anggota', 'F4' => 'Sisa Slot', 'G4' => 'Pengampu Kajian'];
        foreach ($headers as $cell => $text) {
            $summarySheet->setCellValue($cell, $text);
        }

        $summarySheet->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        $row = 5;
        foreach ($kelompokList as $index => $kelompok) {
            $sisa = max(0, $kelompok->kapasitas - $kelompok->anggota->count());
            $pengampuStr = $kelompok->pengampu->map(fn($p) => ($p->kelas?->nama ?? 'Kajian') . ': ' . ($p->tutor?->name ?? 'Belum ada'))->implode('; ');

            $summarySheet->setCellValue('A' . $row, $index + 1);
            $summarySheet->setCellValue('B' . $row, $kelompok->nama);
            $summarySheet->setCellValue('C' . $row, $kelompok->kode ?? '—');
            $summarySheet->setCellValue('D' . $row, $kelompok->kapasitas);
            $summarySheet->setCellValue('E' . $row, $kelompok->anggota->count());
            $summarySheet->setCellValue('F' . $row, $sisa);
            $summarySheet->setCellValue('G' . $row, $pengampuStr ?: 'Belum ditugaskan');

            if ($row % 2 === 0) {
                $summarySheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }
            $summarySheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $summarySheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $summarySheet->getStyle("C{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $summarySheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "daftar-kelompok-{$periode->kode}.xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
