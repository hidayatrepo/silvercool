<?php

namespace App\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\Queries\SQLSelect;
use App\Models\Karyawan;
use App\Models\Bagian;
use App\Models\Shift;
use SilverStripe\ORM\ValidationException;

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
                '"Karyawan"."Flag"',
            ])
            ->setFrom('"Karyawan"')
            ->addLeftJoin('Bagian', '"Karyawan"."BagianID" = "Bagian"."ID"')
            ->addLeftJoin('Shift', '"Karyawan"."ShiftID" = "Shift"."ID"') // FIX: Join dengan ShiftID di Karyawan
            ->setOrderBy('"Karyawan"."ID" ASC');

        $nama = $body['Nama'] ?? '';
        if (!empty($nama)) {
            $query->addWhere(["\"Karyawan\".\"Nama\" LIKE ?" => "%$nama%"]);
        }

        $data = iterator_to_array($query->execute());

        return HTTPResponse::create(json_encode($data, JSON_PRETTY_PRINT))
            ->addHeader('Content-Type', 'application/json; charset=utf-8')
            ->setStatusCode(200);
    }

    // ADD KARYAWAN
    public function add(HTTPRequest $request) {
        
        $body = $request->postVars();

        if (!$body || !isset($body['Nama']) || !isset($body['BagianID']) || !isset($body['ShiftID'])) {
            return $this->jsonResponse(['error' => 'Nama, BagianID, dan ShiftID wajib diisi'], 400);
        }

        try {
            $karyawan = Karyawan::create();
            $karyawan->Nama = $body['Nama'];
            $karyawan->BagianID = $body['BagianID'];
            $karyawan->ShiftID = $body['ShiftID'];
            $karyawan->write();

            return $this->jsonResponse(['message' => 'Karyawan berhasil ditambahkan', 'data' => $karyawan]);
        } catch (ValidationException $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // UPDATE KARYAWAN
    public function update(HTTPRequest $request) {
        
        $body = $request->postVars();

        if (!$body || !isset($body['ID'])) {
            return $this->jsonResponse(['error' => 'ID wajib diisi'], 400);
        }

        $karyawan = Karyawan::get()->byID($body['ID']);

        if (!$karyawan) {
            return $this->jsonResponse(['error' => 'Karyawan tidak ditemukan'], 404);
        }

        try {
            if (isset($body['Nama'])) $karyawan->Nama = $body['Nama'];
            if (isset($body['BagianID'])) $karyawan->BagianID = $body['BagianID'];
            if (isset($body['ShiftID'])) $karyawan->ShiftID = $body['ShiftID'];

            $karyawan->write();

            return $this->jsonResponse(['message' => 'Karyawan berhasil diperbarui', 'data' => $karyawan]);
        } catch (ValidationException $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // DELETE KARYAWAN
    public function delete(HTTPRequest $request) {
        
        $body = $request->postVars();

        if (!$body || !isset($body['ID'])) {
            return $this->jsonResponse(['error' => 'ID wajib diisi'], 400);
        }

        $karyawan = Karyawan::get()->byID($body['ID']);

        if (!$karyawan) {
            return $this->jsonResponse(['error' => 'Karyawan tidak ditemukan'], 404);
        }

        try {
            $karyawan->delete();

            return $this->jsonResponse(['message' => 'Karyawan berhasil dihapus']);
        } catch (ValidationException $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // HELPER FUNCTION: JSON RESPONSE
    private function jsonResponse($data, $status = 200) {
        return HTTPResponse::create(json_encode($data, JSON_PRETTY_PRINT))
            ->addHeader('Content-Type', 'application/json; charset=utf-8')
            ->setStatusCode($status);
    }
}
