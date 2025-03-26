<?php

namespace App\Models;

use SilverStripe\ORM\DataObject;

class Shift extends DataObject
{
    private static $table_name = 'Shift';

    private static $db = [
        'NamaShift' => 'Varchar(255)',
        'Flag'      => 'Boolean(1)' // ✅ Default 1
    ];

    private static $has_many = [
        'Karyawan' => Karyawan::class
    ];

    private static $summary_fields = [
        'ID',
        'NamaShift',
        'Flag'
    ];
}
