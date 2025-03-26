<?php

namespace App\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\Queries\SQLSelect;
use App\Models\Karyawan;
use App\Models\Bagian;
use App\Models\Shift;

class KaryawanController extends Controller {

    private static $allowed_actions = ['index'];

    public function index(HTTPRequest $request) {

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
            ->setOrderBy('"Karyawan"."Nama" ASC');

        $data = iterator_to_array($query->execute());

        return HTTPResponse::create(json_encode($data, JSON_PRETTY_PRINT))
            ->addHeader('Content-Type', 'application/json; charset=utf-8')
            ->setStatusCode(200);


    }
}
