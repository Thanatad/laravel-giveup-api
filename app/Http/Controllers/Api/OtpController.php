<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Otp\Otp as OtpResource;
use App\Http\Resources\Otp\OtpCollection;
use App\Otp;
use Illuminate\Http\Request;
use Validator;

class OtpController extends Controller
{
    public function index(Request $request)
    {

        $mobile = $request->input('mobile');
        $otp = $request->input('otp');
        $ref = $request->input('ref');

        $search = $request->input('search');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($mobile || $otp || $ref) {
            $otp = Otp::orWhere('mobile', $mobile)
                ->orWhere('otp', $otp)
                ->orWhere('ref', $ref)
                ->paginate($perPage);

            if (!$otp->isEmpty()) {
                return response(new OtpCollection($otp));
            } else {
                return response(["error" => "not_found" ,"message" => 'Data not found.'], 404);
            }

        }

        if ($search) {
            $otp = Otp::orWhere('mobile', 'LIKE', '%' . $search . '%')
                ->orWhere('otp', 'LIKE', '%' . $search . '%')
                ->orWhere('ref', 'LIKE', '%' . $search . '%')
                ->paginate($perPage);

            return response(new OtpCollection($otp));
        }

        $otp = Otp::paginate($perPage);
        return response(new OtpCollection($otp));
    }
    public function create()
    {
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|min:10',
            'otp' => 'required|numeric|min:4',
            'ref' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $otp = new Otp;
        $otp->mobile = $request->mobile;
        $otp->otp = $request->otp;
        $otp->ref = $request->ref;
        $otp->save();

        return response(new OtpResource($otp));
    }
}
