<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InviteLog\InviteLog as InviteLogResource;
use App\Http\Resources\InviteLog\InviteLogCollection;
use App\InviteLog;
use Carbon;
use Illuminate\Http\Request;
use Sms;
use Validator;

class InviteLogController extends Controller
{

    private function sms($mobile, $name)
    {
        $sms = new Sms();

        $sms->username = env('THSMS_USERNAME');
        $sms->password = env('THSMS_PASSWORD');

        $message = $name . ' ได้ชวนคุณใช้แอป Thank You https://app-give.com';

        $result = $sms->send('0000', $mobile, $message);

        if ($result[0]) {
            return true;
        }

        return false;
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $userId = $request->input('user_id');
        $mobile = $request->input('mobile');
        $Istoday = $request->input('is_today');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $userId || $mobile || $Istoday) {
            $invite = InviteLog::when($userId, function ($q) use ($userId, $Istoday) {
                $q->where('user_id', $userId)->when($Istoday, function ($q) {
                    $q->whereDate('created_at', Carbon::today());
                });
            })
                ->when($id, function ($q) use ($id) {
                    $q->where('id', $id);
                })
                ->when($mobile, function ($q) use ($mobile) {
                    $q->where('mobile', $mobile);
                })->when($Istoday, function ($q) {
                    $q->whereDate('created_at', Carbon::today());
                })
                ->paginate($perPage);

            if (!$invite->isEmpty()) {
                return response(new InviteLogCollection($invite));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $invite = InviteLog::paginate($perPage);

        return response(new InviteLogCollection($invite));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'user_id' => 'required|numeric|exists:accounts,user_id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $invite = InviteLog::with('account')->create([
            'mobile' => $request->mobile,
            'user_id' => (int) $request->user_id,
        ]);

        $this->sms($request->mobile, $invite->account['name']);

        return response(new InviteLogResource($invite));
    }

    public function destroy($id)
    {
        InviteLog::where('id', $id)->delete();

        return response(['success' => 'Successfully deleted'], 200);
    }
}
