<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ThankPoint;
use Illuminate\Http\Request;
use Validator;

class ThankPointController extends Controller
{

    public function index()
    {
        $tp = ThankPoint::find(1)->point;

        return response(['point' => $tp], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'point' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        ThankPoint::find($id)->update([
            'point' => $request->point,
        ]);

        return response(['success' => 'Successfully updated'], 200);
    }

}
