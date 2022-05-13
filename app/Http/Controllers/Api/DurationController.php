<?php

namespace App\Http\Controllers\Api;

use App\Duration;
use App\Http\Controllers\Controller;
use App\Http\Resources\Duration\DurationCollection;
use Illuminate\Http\Request;
use Validator;

class DurationController extends Controller
{

    public function index(Request $request)
    {
        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $duration = Duration::where('id', $id)
                ->paginate($perPage);

            if (!$duration->isEmpty()) {
                return response(new DurationCollection($duration));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $duration = Duration::paginate($perPage);

        return response(new DurationCollection($duration));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hour' => 'numeric',
            'minute' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $duration = Duration::create([
            'hour' => $request->hour,
            'minute' => $request->minute,
        ]);

        return response($duration, 200);
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
        Duration::where('id', $id)->delete();

        return response(['success' => 'Successfully delete'], 200);
    }
}
