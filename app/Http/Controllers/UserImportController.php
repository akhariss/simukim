<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ImportUsersRequest;
use App\Imports\UsersImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;

class UserImportController extends Controller
{
    /**
     * Proses Import Batch Pengguna dari Excel/CSV
     */
    public function import(ImportUsersRequest $request): RedirectResponse
    {
        $role   = $request->validated()['role'];
        $import = new UsersImport($role);

        Excel::import($import, $request->file('file'));

        $importedCount = count($import->imported);
        $skippedCount  = count($import->skipped);

        $msg = "Import selesai: {$importedCount} pengguna berhasil ditambahkan.";
        if ($skippedCount > 0) {
            $msg .= " {$skippedCount} dilewati (sudah terdaftar): " . implode(', ', $import->skipped) . ".";
        }

        return redirect()->route('users.index', ['role' => $role])
            ->with('success', $msg);
    }

    /**
     * Download Template Format Spreadsheet Excel (.xlsx) untuk Import Batch
     */
    public function downloadTemplate(Request $request, \App\Services\UserExcelTemplateService $service): StreamedResponse
    {
        $role = $request->query('role', 'mahasantri');
        if (!in_array($role, ['mahasantri', 'mudabbir', 'tutor', 'admin', 'all'], true)) {
            $role = 'mahasantri';
        }

        $spreadsheet = $service->generateTemplate($role);

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $filename = "template_import_{$role}_asrama.xlsx";
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
