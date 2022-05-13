<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShareLog\AccountShareLog\ShareLogCollection as AccountShareLogCollection;
use App\Http\Resources\ShareLog\ShareLog as ShareLogResource;
use App\Http\Resources\ShareLog\ShareLogCollection;
use App\ShareLog;
use Illuminate\Http\Request;
use Validator;

class ShareLogController extends Controller
{

    public function index(Request $request)
    {
        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $shareLog = ShareLog::where('id', $id)
                ->paginate($perPage);

            if (!$shareLog->isEmpty()) {
                return response(new ShareLogCollection($shareLog));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $shareLog = ShareLog::paginate($perPage);

        return response(new ShareLogCollection($shareLog));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'post_id' => 'required|numeric|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $shareLog = ShareLog::create([
            'user_id' => $request->user_id,
            'post_id' => $request->post_id,
            'to' => $request->to,
        ]);

        return response(new ShareLogResource($shareLog));
    }

    public function accountShare($id)
    {
        $account = Account::with('posts.shareLog')->where('user_id', $id)->paginate();
        return response(new AccountShareLogCollection($account));
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
