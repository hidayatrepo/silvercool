<?php

namespace App\Models;

use SilverStripe\ORM\DataObject;

class Karyawan extends DataObject
{
    private static $table_name = 'Karyawan'; // ✅ Pastikan ada table_name agar tidak ada error di ORM

    private static $db = [
        'Nama' => 'Varchar(255)', // ✅ Sebaiknya tentukan panjang varchar
        'Flag' => 'Boolean'
    ];

    private static $has_one = [
        'Bagian' => Bagian::class
    ];

    private static $summary_fields = [ // ✅ Tambahkan summary untuk GridField
        'ID',
        'Nama',
        'Flag',
        'Bagian.NamaBagian' => 'Bagian'
    ];
}
