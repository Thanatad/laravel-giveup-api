<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportPost\ReportPost as ReportPostResource;
use App\Http\Resources\ReportPost\ReportPostCollection;
use App\ReportPost;
use Illuminate\Http\Request;
use Validator;

class ReportPostController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->input('id');
        $postId = $request->input('post_id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $postId) {
            $report = ReportPost::with('codes','reporter')
                ->when($id, function ($q) use ($id) {
                    $q->where('id', $id);
                })->when($postId, function ($q) use ($postId) {
                $q->where('post_id', $postId);
            })
                ->paginate($perPage);

            if (!$report->isEmpty()) {
                return response(new ReportPostCollection($report));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $report = ReportPost::with('codes','reporter')->paginate($perPage);

        return response(new ReportPostCollection($report));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|numeric|exists:report_post_codes,id',
            'post_id' => 'required|numeric|exists:posts,id',
            'reporter_id' => 'required|numeric|exists:accounts,user_id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $report = ReportPost::create([
            'reporter_id' => $request->reporter_id,
            'post_id' => $request->post_id,
            'remark' => $request->remark,
            'code' => $request->code,
        ]);

        return response(new ReportPostResource(ReportPost::with('codes')->find($report->id)));
    }

    public function destroy($id)
    {
        ReportPost::where('id', $id)->delete();

        return response(['success' => 'Successfully deleted'], 200);
    }
}
