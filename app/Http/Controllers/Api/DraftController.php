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
     *     path="/api/v1/draft/save",
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
     *
     *     @OA\Parameter(
     *        name="bans",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="array",
     *           @OA\Items(type="integer"),
     *        ),
     *        style="form"
     *    ),
     *
     *     @OA\Parameter(
     *        name="picks",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="array",
     *           @OA\Items(type="integer"),
     *        ),
     *        style="form"
     *    ),
     *
     *     @OA\Parameter(
     *        name="client_name",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *     @OA\Response(
     *        response="200",
     *        description="Inscris une draft dans la base de données et retourne la draft si tout est bon.",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     )
     * )
     */
    public function save(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'teama' => 'required',
                'teamb' => 'required',
                'gamemode_type' => ['required', new EnumValue(GameModeType::class)],
                'bans' => 'required|array',
                'picks' => 'required|array',
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

        // Bans et picks
        foreach ($input['bans'] as $ban) {
            if(!$this->addBan($draft, $ban)) {
                $draft->delete();
                return response()->json(['error' => 'Something went wrong while adding a ban. (Draft will be now delete)'], 500);
            }
        }

        foreach ($input['picks'] as $pick) {
            if(!$this->addPick($draft, $pick)) {
                $draft->delete();
                return response()->json(['error' => 'Something went wrong while adding a pick. (Draft will be now delete)'], 500);
            }
        }

        $success['draft'] = $draft;
        $success['debug'] = GameModeType::NoBans;

        return response()->json(['success' => $success], 200);
    }

    public function addBan(Draft $draft, $map)
    {
        $data = array('map' => $map);
        $validator = Validator::make(
            $data,
            [
                'map' => 'required|integer'
            ]
        );

        if ($validator->fails()) {
            // return response()->json(['error' => $validator->errors()], 401);
            return false;
        }

        // Ici on sécure juste pour ctr
        /*if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }*/

        // $draft = Draft::where('uuid', $uuid)->first();

        // On assume que l'uuid est 100% sûr

        /*if(!$draft) {
            return response()->json(['error' => ['draft' => ['There is no draft using this uuid.']]], 400);
        }*/

        if($draft->gamemode_type->value === GameModeType::NoBans) {
            // Si on est dans la draft en mode no bans
            if($map == -1) {
                // $draft->mapsBanned()->attach($map);
                return true;
            } else {
                return false;
            }
        }

        $map = Map::find($map);

        if(!$map) {
            // return response()->json(['error' => ['map' => ['There is no map using this id.']]], 400);
            return false;
        }

        // Check si la map est pas déjà ban
        /*if($draft->mapsBanned()->where('title', $map->title)->first()) {
            // return response()->json(['error' => 'Map ' . $map->title . ' is already banned in this draft.'], 400);
            return false;
        }*/

        $draft->mapsBanned()->attach($map);

        // return response()->json(['success' => 'Added map ' . $map->title . ' to the ban list of draft ' . $draft->uuid], 200);
        return true;
    }

    public function addPick(Draft $draft, $map)
    {
        $data = array('map' => $map);
        $validator = Validator::make(
            $data,
            [
                'map' => 'required|integer'
            ]
        );

        if ($validator->fails()) {
            //return response()->json(['error' => $validator->errors()], 401);
            return false;
        }

        //$draft = Draft::where('uuid', $uuid)->first();

        /*if(!$draft) {
            return response()->json(['error' => ['draft' => ['There is no draft using this uuid.']]], 400);
        }*/

        $map = Map::find($map);

        if(!$map) {
            // return response()->json(['error' => ['map' => ['There is no map using this id.']]], 400);
            return false;
        }

        // Check si la map est pas déjà pick
        /*if($draft->mapsPicked()->where('title', $map->title)->first()) {
            // return response()->json(['error' => 'Map ' . $map->title . ' is already picked in this draft.'], 400);
            return false;
        }*/

        $draft->mapsPicked()->attach($map);

        // return response()->json(['success' => 'Added map ' . $map->title . ' to the pick list of draft ' . $draft->uuid], 200);
        return true;
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
