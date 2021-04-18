<?php

namespace App\Http\Controllers\API;

use App\Models\Materi;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

class MateriController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');

        $price_from = $request->input('price_form');
        $price_to = $request->input('price_to');

        $rate_from = $request->input('rate_form');
        $rate_to = $request->input('rate_to');

        if ($id) {
            $food = Materi::find($id);

            if ($food) {
                return ResponseFormatter::success(
                    $food, 'Data produk berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null, 'Data produk tidak ada', 404
                );
            }
        }
        $materi = Materi::query();
        if ($name) {
            $materi->where('name', 'like', '%' . $name . '%');
        }
        if ($types) {
            $materi->where('types$types', 'like', '%' . $types . '%');
        }
        if ($price_from) {
            $materi->where('price', '>=', $price_from);
        }
        if ($price_to) {
            $materi->where('price', '<=', $price_to);
        }
        if ($rate_from) {
            $materi->where('rate', '>=', $rate_from);
        }
        if ($rate_to) {
            $materi->where('rate', '<=', $rate_to);
        }

        return ResponseFormatter::success(
            $materi->paginate($limit), 'Data list produk berhasil diambil'
        );
    }
}
