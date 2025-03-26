<?php

namespace App\Models;

use SilverStripe\ORM\DataObject;

class Bagian extends DataObject
{
    private static $table_name = 'Bagian'; // ✅ Tambahkan table_name untuk ORM yang lebih stabil

    private static $db = [
        'NamaBagian' => 'Varchar(255)' // ✅ Sebaiknya tentukan panjang varchar secara eksplisit
    ];

    private static $has_many = [
        'Karyawan' => Karyawan::class
    ];

    private static $summary_fields = [ // ✅ Tambahkan summary_fields agar bisa tampil di CMS
        'ID',
        'NamaBagian'
    ];
}
