<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Controller;
use App\Http\Resources\Report\Report as ReportResource;
use App\Http\Resources\Report\ReportCollection;
use App\Report;
use Carbon;
use Illuminate\Http\Request;
use Validator;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        $id = $request->input('id');
        $userId = $request->input('user_id');
        $status = $request->input('status');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $userId || $status) {
            $report = Report::with('codes', 'account', 'reporter')->when($id, function ($q) use ($id) {
                $q->where('id', $id);
            })->when($userId, function ($q) use ($userId, $status) {
                $q->where('user_id', $userId)->when($status, function ($q) use ($status) {
                    $q->where('status', $status);
                });
            })->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
                ->paginate($perPage);

            if (!$report->isEmpty()) {
                return response(new ReportCollection($report));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $report = Report::with('codes', 'account', 'reporter')->paginate($perPage);

        return response(new ReportCollection($report));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|numeric|exists:report_codes,id',
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'reporter_id' => 'required|numeric|exists:accounts,user_id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $report = Report::create([
            'reporter_id' => $request->reporter_id,
            'user_id' => $request->user_id,
            'remark' => $request->remark,
            'code' => $request->code,
            'status' => $request->status ? $request->status : 1,
        ]);

        $this->calReportDate($request->user_id);

        return response(new ReportResource(Report::with('codes')->find($report->id)));
    }

    public function update(Request $request, $id)
    {

        $report = Report::find($id);

        $request->has('status') ? $report->status = $request->status : '';
        $report->save();

        $this->calReportDate($report->user_id);

        return response(['success' => 'Successfully updated'], 200);
    }

    private function calReportDate(int $userId)
    {
        $aReport = Report::where('user_id', $userId)->where('status', 2)->pluck('id');

        if (count($aReport) == 3) {
            $addDate = Carbon::now()->addDay(7)->format('Y-m-d');
            Account::where('user_id', $userId)->update([
                'report_end_date' => $addDate,
            ]);

            Report::whereIn('id', $aReport)->update([
                'status' => 0,
            ]);

        }
    }
}
