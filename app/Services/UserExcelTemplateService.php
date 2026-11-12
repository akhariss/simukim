<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UserExcelTemplateService
{
    private function getRoleSchemas(): array
    {
        return [
            'mahasantri' => [
                'title' => 'Mahasantri',
                'headers' => ['nim', 'nama', 'jenis_kelamin', 'prodi', 'angkatan', 'no_hp', 'email', 'password'],
                'instructions' => [
                    '[Wajib] NIM Mahasantri', '[Wajib] Nama Lengkap', '[Wajib] L atau P',
                    '[Opsional] Program Studi', '[Opsional] Angkatan (2025)',
                    '[Opsional] No WhatsApp', '[Opsional] Email (@student.uinssc.ac.id)',
                    '[Opsional] Password lokal (kosong = SSO)',
                ],
                'samples' => [
                    ['250100001', 'Ahmad Fauzan Al-Banjari', 'L', 'Ilmu Al-Qur\'an dan Tafsir', '2025', '081201000000', '', ''],
                    ['250100002', 'Fatimah Azzahra', 'P', 'Pendidikan Agama Islam', '2025', '081201000001', '', ''],
                ],
            ],
            'mudabbir' => [
                'title' => 'Mudabbir',
                'headers' => ['nim', 'nama', 'jenis_kelamin', 'prodi', 'angkatan', 'no_hp', 'email', 'password'],
                'instructions' => [
                    '[Wajib] NIM Mahasiswa Pembina', '[Wajib] Nama Lengkap', '[Wajib] L atau P (Gedung Putra/Putri)',
                    '[Opsional] Program Studi', '[Opsional] Angkatan',
                    '[Opsional] No WhatsApp Pembina', '[Opsional] Email Resmi',
                    '[Opsional] Password lokal (kosong = SSO)',
                ],
                'samples' => [
                    ['230100055', 'Muhammad Farhan Syah', 'L', 'Pendidikan Bahasa Arab', '2023', '081300001122', '', ''],
                    ['230100056', 'Nur Azizah Hanum', 'P', 'Ilmu Hadis', '2023', '081300001133', '', ''],
                ],
            ],
            'tutor' => [
                'title' => 'Tutor Pengajar',
                'headers' => ['nip_nim', 'nama', 'jenis_kelamin', 'unit_prodi', 'no_hp', 'email', 'password'],
                'instructions' => [
                    '[Wajib] NIP Dosen atau NIM Tutor', '[Wajib] Nama Lengkap & Gelar', '[Opsional] L atau P',
                    '[Opsional] Unit / Fakultas / Prodi Asal', '[Opsional] No WhatsApp Pengajar',
                    '[Opsional] Email Resmi Kampus', '[Opsional] Password lokal (kosong = SSO)',
                ],
                'samples' => [
                    ['198507152010121003', 'Dr. H. Maimun, M.Ag.', 'L', 'Fakultas Tarbiyah', '081322334455', 'maimun@uinssc.ac.id', ''],
                    ['220100099', 'Ustadzah Sarah Maulida, S.Ag.', 'P', 'Fakultas Ushuluddin', '081399887766', 'sarah@uinssc.ac.id', ''],
                ],
            ],
            'admin' => [
                'title' => 'Administrator',
                'headers' => ['nip_id', 'nama', 'email', 'no_hp', 'password'],
                'instructions' => [
                    '[Wajib] NIP / ID Pengelola (ADM-01)', '[Wajib] Nama Lengkap',
                    '[Wajib] Email Akun Admin', '[Opsional] No WhatsApp',
                    '[Opsional] Password lokal (kosong = SSO)',
                ],
                'samples' => [
                    ['ADM-01', 'Ust. Syarif Hidayatullah, M.Pd.', 'syarif@uinssc.ac.id', '081299001122', 'rahasia123'],
                ],
            ],
        ];
    }

    public function generateTemplate(string $role = 'mahasantri'): Spreadsheet
    {
        $schemas = $this->getRoleSchemas();
        $spreadsheet = new Spreadsheet();

        if ($role === 'all') {
            $sheetIdx = 0;
            foreach ($schemas as $rKey => $schema) {
                $sheet = $sheetIdx === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
                $this->buildSheet($sheet, $schema);
                $sheetIdx++;
            }
            $spreadsheet->setActiveSheetIndex(0);
            return $spreadsheet;
        }

        $schema = $schemas[$role] ?? $schemas['mahasantri'];
        $sheet = $spreadsheet->getActiveSheet();
        $this->buildSheet($sheet, $schema);
        return $spreadsheet;
    }

    private function buildSheet($sheet, array $schema): void
    {
        $sheet->setTitle($schema['title']);
        $sheet->setShowGridlines(true);
        $headers = $schema['headers'];
        $instructions = $schema['instructions'];
        $samples = $schema['samples'];

        foreach ($headers as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}1", $header);
            $sheet->setCellValue("{$colLetter}2", $instructions[$colIdx] ?? '');
            if (in_array($header, ['nim', 'nip_nim', 'nip_id', 'no_hp', 'angkatan'], true)) {
                $sheet->getStyle("{$colLetter}1:{$colLetter}500")->getNumberFormat()->setFormatCode('@');
            }
        }

        $rowNum = 3;
        foreach ($samples as $sample) {
            foreach ($sample as $colIdx => $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValueExplicit("{$colLetter}{$rowNum}", $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $rowNum++;
        }

        $totalCols = count($headers);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '475569'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $sheet->getStyle("A1:{$lastCol}" . ($rowNum - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        for ($i = 1; $i <= $totalCols; $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }
}
