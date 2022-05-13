<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Province\Province as ProvinceResource;
use App\Http\Resources\Province\ProvinceCollection;
use App\Province;
use App\Amphure;
use App\District;
use App\Geographie;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{

    public function geographie(Request $request)
    {

        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $province = Geographie::with('provinces')->Where('id', $id)
                ->paginate($perPage);

            if (!$province->isEmpty()) {
                return response(new ProvinceCollection($province));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $province = Geographie::with('provinces')->paginate($perPage);
        return response(new ProvinceCollection($province));

    }

    public function provence(Request $request)
    {

        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $province = Province::with('amphures.districts')->Where('id', $id)
                ->paginate($perPage);

            if (!$province->isEmpty()) {
                return response(new ProvinceCollection($province));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $province = Province::with('amphures.districts')->paginate($perPage);
        return response(new ProvinceCollection($province));

    }

    public function amphure(Request $request)
    {

        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $province = Amphure::with('provinces')->Where('id', $id)
                ->paginate($perPage);

            if (!$province->isEmpty()) {
                return response(new ProvinceCollection($province));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $province = Amphure::with('provinces')->paginate($perPage);
        return response(new ProvinceCollection($province));

    }

    public function district(Request $request)
    {

        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $province = District::with('amphures')->Where('id', $id)
                ->paginate($perPage);

            if (!$province->isEmpty()) {
                return response(new ProvinceCollection($province));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $province = District::with('amphures')->paginate($perPage);
        return response(new ProvinceCollection($province));

    }

}
