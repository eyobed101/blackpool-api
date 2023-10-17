<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\BetCombination;
use App\Models\User;

class BetController extends Controller
{
    public function placeBet(Request $request)
    {
        $user = auth()->user();

        $isComboBet = $request->has('is_combo_bet') && $request->input('is_combo_bet');

        if ($isComboBet) {

        $selectedEvents = $request->input('selected_events');
        $selectedOutcomes = $request->input('selected_outcomes');
        $betAmounts = $request->input('bet_amounts');

        $betCombination = new BetCombination();
        $betCombination->user_id = $user->id;
        $betCombination->status = 'PROCESSING'; 
        $betCombination->save();

        for ($i = 0; $i < count($selectedEvents); $i++) {
            $bet = new Bet();
            $bet->user_id = $user->id;
            $bet->bet_combination_id = $betCombination->id;
            $bet->bet_type = 'COMBO';
            $bet->event_id = $selectedEvents[$i];
            $bet->outcome = $selectedOutcomes[$i];
            $bet->bet_amount = $betAmounts[$i];
            $bet->potential_payout = $betAmounts[$i] * $selectedPrice;
            $bet->status = 'PROCESSING';
            $bet->save();
        }

        $user->balance -= array_sum($betAmounts);
        $user->save();
    }

        $request->validate([
            'event_id' => 'required',
            'outcome' => 'required',
            'bet_amount' => 'required|numeric|min:0',
        ]);

        

        $eventId = $request->input('event_id');
        $selectedOutcome = $request->input('outcome');
        $selectedPrice = $request->input('price'); 

        $betAmount = $request->input('bet_amount');
        $potentialPayout = $betAmount * $selectedPrice;

        $bet = new Bet();
        $bet->user_id = $user->id;
        $bet->event_id = $eventId;
        $bet->bet_type = 'SINGLE';
        $bet->outcome = $selectedOutcome;
        $bet->bet_amount = $betAmount;
        $bet->potential_payout = $potentialPayout;
        $bet->status = 'PROCESSING'; 
        $bet->save();

        $user->balance -= $betAmount;
        $user->save();

        return response()->json(['message' => 'Bet placed successfully']);
    }
}

