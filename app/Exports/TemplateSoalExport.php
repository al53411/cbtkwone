<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TemplateSoalExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new TemplateSoalDataSheet(),
            new TemplateSoalPetunjukSheet(),
        ];
    }
}
