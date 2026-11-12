<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterLaporanExport implements WithMultipleSheets
{
    public function __construct(
        protected array $sheetsData
    ) {}

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->sheetsData as $title => $data) {
            $sheets[] = new GenericArraySheetExport($title, $data['headers'], $data['rows']);
        }

        return $sheets;
    }
}

