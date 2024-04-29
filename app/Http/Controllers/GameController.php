<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameController extends Controller
{

    public function index()
    {
        $games = Game::search()->latest('games.id')->paginate(request()->limit ?? 10);

        return response()->json([
            'success' => true,
            'data' => [
                'games' => $games
            ]
        ], $games->total() > 0 ? 200 : 204);
    }


    public function startNewGames(){

        //hare can be put all the Business logic

        DB::beginTransaction();
        try {
            $game = Game::create([
                'uuid' => Str::uuid(),
            ]);
    
            $users = User::get();
    
            foreach ($users as $key => $user) {  

                if ($user->balance < 50) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'error' => 'A user does not have enough balance'
                    ], 400);
                }

                $user->balance -= 50;
                $user->save();         
                $user->games()->attach($game->id);
            }
    
            DB::commit();
            return response()->json([
                'success' => true,
                'data' => [
                    'game'    => Game::find($game->id),
                    'message' => 'Game started successfully'
                ]
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $th
            ],500);
        }
    }


    public function show($id){

        $game = Game::with('users')->find($id);

        return response()->json([
            'success' => true,
            'data' => [
                    'game' => $game,
                ]
        ], 200);
    }


    public function stopGame($uuid){

        //hare can be put all the Business logic
        DB::beginTransaction();

        try {
            $game = Game::where('uuid', $uuid)->first();
            $game->active = false;
            $game->save();

            $gameUsers = GameUser::where('game_id', $game->id)->inRandomOrder()->get();

            foreach ($gameUsers as $key => $gameUser) {

                if ($key == 0) {
                    $gameUser->user->balance += 50;
                    $gameUser->user->save();
                }

                $gameUser->position = $key+1;
                $gameUser->save();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Game stopped successfully'
                ]
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $th->getMessage()
            ],500);
        }
    }


    public function gameLeaderBoard(){

        $leaderboard = DB::table('game_users')
                        ->select(
                            'users.id as user_id',
                            'users.name',
                            'users.balance as user_current_balance',
                            DB::raw('SUM(CASE WHEN position = 1 THEN 1 ELSE 0 END) AS total_won_games'),
                            DB::raw('COUNT(*) AS total_games'),
                            DB::raw('SUM(CASE WHEN position != 1 THEN 1 ELSE 0 END) AS total_lost_games'),
                            DB::raw('SUM(CASE WHEN position != 1 THEN 50 ELSE 0 END) AS total_lost_money')
                        )
                        ->leftJoin('users', 'game_users.user_id', '=', 'users.id')
                        ->groupBy('users.id', 'users.name', 'users.balance')
                        ->orderBy('total_won_games', 'desc')
                        ->paginate(request()->limit ?? 10);

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard
            ]
        ], 200);
    }
}
