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

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Tambahkan UploadField untuk foto
        $fotoField = UploadField::create('Foto', 'Foto Karyawan')
            ->setFolderName('karyawan') // Simpan di `assets/karyawan/`
            ->setAllowedExtensions(['jpg', 'jpeg', 'png']) // Batasi format gambar
            ->setMaxFileSize(200 * 1024); // Maksimal 200KB

        $fields->addFieldToTab('Root.Main', $fotoField);

        return $fields;
    }

    // Override save method if necessary to store the file name instead of ID
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Jika tidak ada file foto, set FotoNama ke default.png
        if (!$this->FotoNama) {
            $this->FotoNama = 'default.png';
        }

        // Jika ada file foto yang diupload, simpan nama file dengan format Unix timestamp + tanggal
        if ($this->Foto && $this->Foto->exists()) {
            // Generate a unique file name (e.g., silvercool_YYYYMMDD_HHMMSS.jpg)
            $newFileName = $this->generateUniqueFileName($this->Foto->getExtension());
            $this->Foto->setName($newFileName);
            $this->Foto->write(); // Menyimpan perubahan pada file
            $this->FotoNama = $newFileName; // Simpan nama file baru

            // Membuat thumbnail
            $this->createThumbnail($this->Foto);
        }
    }

    // Fungsi untuk menghasilkan nama file unik berdasarkan waktu saat ini
    private function generateUniqueFileName($extension)
    {
        // Ambil timestamp Unix saat ini
        $timestamp = time();

        // Ambil tanggal dan waktu dalam format YYYYMMDD_HHMMSS
        $dateTime = date("Ymd_His", $timestamp);

        // Gabungkan string acak dengan tanggal dan waktu
        $uniqueName = 'silvercool_' . $dateTime . '.' . $extension;

        return $uniqueName;
    }

    // Fungsi untuk membuat thumbnail gambar
    private function createThumbnail($image)
    {
        $thumbnailFolder = 'karyawan/thum/';
        $fileExtension = $image->getExtension();
        $originalFileName = $image->getName();
        $thumbnailName = 'thum_' . $originalFileName;

        // Buat folder jika belum ada
        $thumbnailPath = Director::baseFolder() . '/assets/' . $thumbnailFolder . $thumbnailName;

        // Cek apakah folder thumbnail sudah ada, jika tidak buat
        if (!is_dir(Director::baseFolder() . '/assets/' . $thumbnailFolder)) {
            mkdir(Director::baseFolder() . '/assets/' . $thumbnailFolder, 0777, true);
        }

        // Ambil gambar dengan pustaka Intervention
        $imagePath = $image->getFullPath();
        $interventionImage = \Intervention\Image\ImageManagerStatic::make($imagePath);

        // Tentukan ukuran thumbnail, misalnya 150x150 pixel
        $interventionImage->resize(150, 150);

        // Simpan thumbnail di folder `karyawan/thum/`
        $interventionImage->save($thumbnailPath, 75); // Kompresi gambar thumbnail dengan kualitas 75%

        // Menyimpan nama file thumbnail jika diperlukan (optional)
        return $thumbnailPath;
    }
}

