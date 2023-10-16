<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\User;

class BetController extends Controller
{
    public function placeBet(Request $request)
    {


        $isComboBet = $request->has('is_combo_bet') && $request->input('is_combo_bet');


        $request->validate([
            'event_id' => 'required',
            'outcome' => 'required',
            'bet_amount' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        $eventId = $request->input('event_id');
        $selectedOutcome = $request->input('outcome');
        $selectedPrice = $request->input('price'); 

        $betAmount = $request->input('bet_amount');
        $potentialPayout = $betAmount * $selectedPrice;

        $bet = new Bet();
        $bet->user_id = $user->id;
        $bet->event_id = $eventId;
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

