<?php

namespace App\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\Queries\SQLSelect;
use App\Models\Karyawan;
use App\Models\Bagian;
use App\Models\Shift;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Assets\Image; // ✅ Tambahkan ini
use SilverStripe\Assets\Upload; // ✅ Tambahkan ini
use SilverStripe\Core\Convert;

class KaryawanController extends Controller {

    private static $allowed_actions = ['index', 'add', 'update', 'delete'];

    // GET ALL DATA
    public function index(HTTPRequest $request) {
        $body = $request->postVars();

        $query = SQLSelect::create()
            ->setSelect([
                '"Karyawan"."ID"',
                '"Karyawan"."Nama"',
                '"Bagian"."NamaBagian" AS "Bagian"',
                '"Shift"."NamaShift" AS "Shift"',
                'CONCAT(\'/assets/\', "Karyawan"."FotoNama") AS "Foto"',
                '"Karyawan"."Flag"',
            ])
            ->setFrom('"Karyawan"')
            ->addLeftJoin('Bagian', '"Karyawan"."BagianID" = "Bagian"."ID"')
            ->addLeftJoin('Shift', '"Karyawan"."ShiftID" = "Shift"."ID"')
            ->setOrderBy('"Karyawan"."ID" ASC');

        // Bersihkan input Nama sebelum dipakai
        $nama = !empty($body['Nama']) ? trim(Convert::raw2sql($body['Nama'])) : null;

        if ($nama) {
            $query->addWhere(["\"Karyawan\".\"Nama\" LIKE ?" => "%$nama%"]);
        }

        $data = iterator_to_array($query->execute());

        return $this->jsonResponse(true, 'Data karyawan berhasil diambil', $data);
    }

    // ADD DATA
    public function add(HTTPRequest $request) {
        $body = $request->postVars();
        $files = $_FILES; // Ambil file yang diunggah

        if (empty($body['Nama']) || empty($body['BagianID']) || empty($body['ShiftID'])) {
            return $this->jsonResponse(false, 'Nama, BagianID, dan ShiftID wajib diisi');
        }

        $data = [
            'Nama' => $body['Nama'],
            'BagianID' => $body['BagianID'],
            'ShiftID' => $body['ShiftID']
        ];

        DB::get_conn()->transactionStart();
        try {
            $karyawan = Karyawan::create()->update($data);
            $karyawan->write();

            // **🔹 Jika ada file gambar yang diunggah**
            if (!empty($files['Foto']['name'])) {
                // **Upload gambar menggunakan Upload class**
                $foto = Image::create(); // Create an empty Image object
                $upload = new Upload();

                // Try to upload the file
                $upload->loadIntoFile($files['Foto'], $foto, 'karyawan');

                // Pastikan file valid
                if ($foto && $foto->exists()) { // Ensure $foto is a valid Image object
                    // Simpan nama file, bukan FotoID
                    $karyawan->FotoNama = $foto->getFilename(); // Save the file name, not the ID
                    $karyawan->write();
                }
            }

            DB::get_conn()->transactionEnd();
            return $this->jsonResponse(true, 'Karyawan berhasil ditambahkan');
        } catch (ValidationException $e) {
            DB::get_conn()->transactionRollback();
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    // UPDATE DATA
    public function update(HTTPRequest $request) {
        $body = $request->postVars();
        $files = $_FILES; // Ambil file yang diunggah

        if (empty($body['ID'])) {
            return $this->jsonResponse(false, 'ID wajib diisi');
        }

        $karyawan = Karyawan::get()->byID($body['ID']);
        if (!$karyawan) {
            return $this->jsonResponse(false, 'Karyawan tidak ditemukan');
        }

        $data = [];
        if (!empty($body['Nama'])) $data['Nama'] = $body['Nama'];
        if (!empty($body['BagianID'])) $data['BagianID'] = $body['BagianID'];
        if (!empty($body['ShiftID'])) $data['ShiftID'] = $body['ShiftID'];

        DB::get_conn()->transactionStart();
        try {
            $karyawan->update($data);
            $karyawan->write();

            // **🔹 Jika ada file gambar yang diunggah**
            if (!empty($files['Foto']['name'])) {
                // **Upload gambar menggunakan Upload class**
                $foto = Image::create(); // Create an empty Image object
                $upload = new Upload();

                // Try to upload the file
                $upload->loadIntoFile($files['Foto'], $foto, 'karyawan');

                // Pastikan file valid
                if ($foto && $foto->exists()) { // Ensure $foto is a valid Image object
                    // Simpan nama file, bukan FotoID
                    $karyawan->FotoNama = $foto->getFilename(); // Save the file name, not the ID
                    $karyawan->write();
                }
            }

            DB::get_conn()->transactionEnd();
            return $this->jsonResponse(true, 'Karyawan berhasil diperbarui');
        } catch (ValidationException $e) {
            DB::get_conn()->transactionRollback();
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    // DELETE DATA
    public function delete(HTTPRequest $request) {
        $body = $request->postVars();

        $id = isset($body['ID']) ? (int) $body['ID'] : 0;
        if ($id <= 0) {
            return $this->jsonResponse(false, 'ID wajib diisi');
        }

        $karyawan = Karyawan::get()->byID($id);
        if (!$karyawan) {
            return $this->jsonResponse(false, 'Karyawan tidak ditemukan');
        }

        DB::get_conn()->transactionStart();
        try {
            $karyawan->delete();

            DB::get_conn()->transactionEnd();
            return $this->jsonResponse(true, 'Karyawan berhasil dihapus');
        } catch (ValidationException $e) {
            DB::get_conn()->transactionRollback();
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    // HELPER FUNCTION: JSON RESPONSE
    private function jsonResponse($result, $message, $data = null) {
        $response = [
            'result' => $result,
            'message' => $message
        ];
        if ($result && $data !== null) {
            $response['data'] = $data;
        }

        return HTTPResponse::create(json_encode($response, JSON_PRETTY_PRINT))
            ->addHeader('Content-Type', 'application/json; charset=utf-8')
            ->setStatusCode($result ? 200 : 400);
    }
}
