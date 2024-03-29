<?php

namespace App\Http\Controllers;

use App\Helpers\LeagueHelper;
use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Match as MatchResource;
use App\Http\Resources\League as LeagueResource;

class LeagueController extends Controller {

    protected $leagueHelper;

    public function __construct( LeagueHelper $helper ) {
        $this->leagueHelper = $helper;
    }

    public function index( ) {
        return response()->json( [
            'status' => 'ok',
            'data' => LeagueResource::collection( $this->leagueHelper->getAllLeagues() )
        ] );
    }

    public function createLeague( Request $request ) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);
        if ($validator->fails()) return response()->json([
            'status' => 'failed',
            'data' => null
        ]);
        return response()->json([
            'status' => 'ok',
            'data' => new LeagueResource( $this->leagueHelper->createNewLeague( $request->get('name') ) )
        ]);
    }

    public function resetLeague( $leagueId ) {
        $league = League::find( $leagueId );
        if ( !$league ) {
            return response()->json([
                'status' => 'fail',
                'data' => 'invalid league id'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => new LeagueResource( $this->leagueHelper->resetLeague( $leagueId ) )
        ]);
    }


    public function getLeagueGames( $leagueId ) {
        $league = League::find( $leagueId );
        if ( !$league ) {
            return response()->json([
                'status' => 'fail',
                'data' => 'invalid league id'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => MatchResource::collection( $this->leagueHelper->getLeagueGames( $leagueId ) )
        ]);
    }

    public function getWeekGames( $leagueId, $week ) {
        $league = League::find( $leagueId );
        if ( !$league ) {
            return response()->json([
                'status' => 'fail',
                'data' => 'invalid league id'
            ]);
        }

        if ( intval($week) < 1 || intval($week) > 6 ) {
            return response()->json([
                'status' => 'fail',
                'data' => 'invalid week'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => MatchResource::collection( $this->leagueHelper->getWeekGames( $leagueId, $week ) )
        ]);
    }

    public function play ( $leagueId ) {
        $league = League::find( $leagueId );
        if ( !$league ) {
            return response()->json([
                'status' => 'fail',
                'data' => 'invalid league id'
            ]);
        }

        $this->leagueHelper->playWeekGames( $leagueId );
        return response()->json([
            'status' => 'ok',
            'data' => null
        ]);
    }

    public function playAll ( $leagueId ) {
        $league = League::find( $leagueId );
        if ( !$league ) {
            return response()->json([
                'status' => 'fail',
                'data' => 'invalid league id'
            ]);
        }

        $this->leagueHelper->playAllGames( $leagueId );
        return response()->json([
            'status' => 'ok',
            'data' => null
        ]);
    }

    public function standings( $leagueId ) {
        $league = League::find( $leagueId );
        if ( !$league ) {
            return response()->json([
                'status' => 'fail',
                'data' => 'invalid league id'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $this->leagueHelper->getLeagueStandings( $leagueId )
        ]);
    }
}
