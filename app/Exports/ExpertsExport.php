<?php

namespace App\Exports;

use App\Models\Expert;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExpertsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Expert::all();
    }
}
