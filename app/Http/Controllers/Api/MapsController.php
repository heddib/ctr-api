<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Map;
use Illuminate\Http\Request;

class MapsController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/maps",
     *     tags={"Maps"},
     *
     *     @OA\Response(
     *        response="200",
     *        description="Retourne un tableau des maps.",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     )
     * )
     */
    public function getMaps()
    {
        return response()->json(["maps" => Map::all()], 200);
    }

}
