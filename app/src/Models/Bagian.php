<?php

namespace App\Models;

use SilverStripe\ORM\DataObject;

class Bagian extends DataObject
{
    private static $table_name = 'Bagian';

    private static $db = [
        'NamaBagian' => 'Varchar(255)',
        'Flag'       => 'Boolean(1)' // ✅ Default 1
    ];

    private static $has_many = [
        'Karyawan' => Karyawan::class
    ];

    private static $summary_fields = [
        'ID',
        'NamaBagian',
        'Flag'
    ];
}
