<?php

namespace App\Http\Controllers\Api;

use App\Draft;
use BenSampo\Enum\Rules\EnumValue;
use App\Enums\GameModeType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Map;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DraftController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/draft/create",
     *     tags={"Draft"},
     *
     *     @OA\Parameter(
     *        name="teama",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *     @OA\Parameter(
     *        name="teamb",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *     @OA\Parameter(
     *        name="gamemode",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),

     *     @OA\Response(
     *        response="200",
     *        description="Inscris une draft dans la base de données et retourne son id.",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     )
     * )
     */
    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'teama' => 'required',
                'teamb' => 'required',
                'gamemode_type' => ['required', new EnumValue(GameModeType::class)],
                'client_name' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = Auth::user();
        if(!$user) {
            return response()->json(['error' => ['user' => ['Please login to continue.']]], 401);
        }

        // Ici on sécure juste pour ctr
        if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        if (!$user->tokenCan('admin')) {
            return response()->json(['error' => ['token' => ['Unauthorized token for this action.']]], 401);
        }

        $input = $request->all();
        $input['user_id'] = $user->id;
        $draft = Draft::create($input);

        $success['draft'] = $draft;

        return response()->json(['success' => $success], 200);
    }

    public function addBan(Request $request, $uuid)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'client_name' => 'required',
                'map' => 'required|integer'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Ici on sécure juste pour ctr
        if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        $draft = Draft::where('uuid', $uuid)->first();

        if(!$draft) {
            return response()->json(['error' => ['draft' => ['There is no draft using this uuid.']]], 400);
        }

        $map = Map::find($request->map);

        if(!$map) {
            return response()->json(['error' => ['map' => ['There is no map using this id.']]], 400);
        }

        // Check si la map est pas déjà ban
        if($draft->mapsBanned()->where('title', $map->title)->first()) {
            return response()->json(['error' => 'Map ' . $map->title . ' is already banned in this draft.'], 400);
        }

        $draft->mapsBanned()->attach($map);

        return response()->json(['success' => 'Added map ' . $map->title . ' to the ban list of draft ' . $draft->uuid], 200);
    }

    public function addPick(Request $request, $uuid)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'client_name' => 'required',
                'map' => 'required|integer'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Ici on sécure juste pour ctr
        if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        $draft = Draft::where('uuid', $uuid)->first();

        if(!$draft) {
            return response()->json(['error' => ['draft' => ['There is no draft using this uuid.']]], 400);
        }

        $map = Map::find($request->map);

        if(!$map) {
            return response()->json(['error' => ['map' => ['There is no map using this id.']]], 400);
        }

        // Check si la map est pas déjà pick
        if($draft->mapsPicked()->where('title', $map->title)->first()) {
            return response()->json(['error' => 'Map ' . $map->title . ' is already picked in this draft.'], 400);
        }

        $draft->mapsPicked()->attach($map);

        return response()->json(['success' => 'Added map ' . $map->title . ' to the pick list of draft ' . $draft->uuid], 200);
    }

    public function getDraft(Request $request, $uuid)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'client_name' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Ici on sécure juste pour ctr
        if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        $draft = Draft::where('uuid',  $uuid)->first();

        if(!$draft) {
            return response()->json(['error' => ['draft' => ['There is no draft using this uuid.']]], 400);
        }

        return response()->json(['draft' => $draft], 200);
    }

    public function getBans(Request $request, $uuid)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'client_name' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Ici on sécure juste pour ctr
        if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        $draft = Draft::where('uuid',  $uuid)->first();

        if(!$draft) {
            return response()->json(['error' => ['draft' => ['There is no draft using this uuid.']]], 400);
        }

        $bans = $draft->mapsBanned;

        return response()->json(['bans' => $bans], 200);
    }

    public function getPicks(Request $request, $uuid)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'client_name' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Ici on sécure juste pour ctr
        if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        $draft = Draft::where('uuid',  $uuid)->first();

        if(!$draft) {
            return response()->json(['error' => ['draft' => ['There is no draft using this uuid.']]], 400);
        }

        $picks = $draft->mapsPicked;

        return response()->json(['picks' => $picks], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/drafts",
     *     tags={"Drafts"},
     *
     *     @OA\Response(
     *        response="200",
     *        description="Retourne un tableau des drafts.",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     )
     * )
     */
    public function getDrafts()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => ['user' => ['You must be logged in to do this.']]], 401);
        }

        if (!$user->tokenCan('admin')) {
            return response()->json(['error' => ['token' => ['Unauthorized token for this action.']]], 401);
        }

        return response()->json(["drafts" => Draft::all()], 200);
    }
}
