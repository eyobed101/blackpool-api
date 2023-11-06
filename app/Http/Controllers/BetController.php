<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\BetCombination;
use App\Models\User;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

class BetController extends Controller
{
    public function placeBet(Request $request)
    {
        $user = auth()->user();
        try {

            $selectedPrice = $request->input("selected_price");

            $isComboBet = $request->has('is_combo_bet') && $request->input('is_combo_bet');

            if ($isComboBet) {
                $selectedEvents = $request->input('event_id');
                $selectedOutcomes = $request->input('outcome');
                $betAmounts = $request->input('bet_amount');


                


                $betCombination = BetCombination::firstOrCreate([
                    "id" => Str::random(32),
                    "user_id" => $user->id,
                    "status" => 'PROCESSING'
                ]);
                for ($i = 0; $i < count($selectedEvents); $i++) {
                    $ID = Str::random(32);

                    $bet = Bet::firstOrCreate([
                    'id'=> $ID,
                    "user_id" =>  $user->id,
                    "bet_combination_id" => $betCombination->id,
                    "bet_type" => 'COMBO',
                    "event_id" => $selectedEvents[$i],
                    "outcome" => $selectedOutcomes[$i],
                    "bet_amount" => $betAmounts[$i],
                    "potential_payout" => $betAmounts[$i] * $selectedPrice,
                    "status" => 'PROCESSING',
                    ]);
                }



                

            } else {
                $eventId = $request->input('event_id');
                $selectedOutcome = $request->input('outcome');

                $betAmount = $request->input('bet_amount');

                $ID = Str::random(32);

                $bet = Bet::firstOrCreate([
                    'id'=> $ID,
                    "user_id" =>  $user->id,
                    "bet_type" => 'SINGLE',
                    "event_id" => $eventId[0],
                    "outcome" => $selectedOutcome[0],
                    "bet_amount" => $betAmount[0],
                    "potential_payout" => $betAmount[0] * $selectedPrice,
                    "status" => 'PROCESSING',
                    ]);
            }
           
            User::where('id', $user->id)->decrement('balance', $selectedPrice);


            return response()->json(['message' => 'Bet placed successfully']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json($e->getMessage(), 500);
        }
    }
}

