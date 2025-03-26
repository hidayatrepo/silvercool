<?php

namespace App\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Control\Director;
use Intervention\Image\Image as InterventionImage; // Untuk menggunakan pustaka GD atau ImageMagick

class Karyawan extends DataObject
{
    private static $table_name = 'Karyawan'; // Pastikan ada table_name agar tidak ada error di ORM

    private static $db = [
        'Nama' => 'Varchar(255)', // Tentukan panjang varchar
        'Flag' => 'Boolean(1)',
        'FotoNama' => 'Varchar(255)' // Menyimpan nama file foto
    ];

    private static $has_one = [
        'Bagian' => Bagian::class,
        'Shift'  => Shift::class,
    ];

    private static $summary_fields = [
        'ID',
        'Nama',
        'Flag',
        'Bagian.NamaBagian' => 'Bagian',
        'Shift.NamaBagian' => 'Shift',
        'FotoNama' => 'Foto' // Tampilkan nama file foto di CMS
    ];
}
