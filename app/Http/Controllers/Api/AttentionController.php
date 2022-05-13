<?php

namespace App\Http\Controllers\Api;

use App\Attention;
use App\Http\Controllers\Controller;
use App\Http\Resources\Attention\Attention as AttentionResource;
use App\Http\Resources\Attention\AttentionCollection;
use Illuminate\Http\Request;
use Validator;

class AttentionController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $attention = Attention::where('id', $id)
                ->paginate($perPage);

            if (!$attention->isEmpty()) {
                return response(new AttentionCollection($attention));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $attention = Attention::paginate($perPage);

        return response(new AttentionCollection($attention));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $attention = Attention::create([
            'name' => $request->name,
        ]);

        return response(new AttentionResource($attention));

    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $attention = Attention::find($id);

        $attention->name = $request->name;
        $attention->save();

        return response(new AttentionResource($attention));
    }

    public function destroy($id)
    {
        Attention::where('id', $id)->delete();

        return response(['success' => 'Successfully deleted'], 200);
    }
}
