<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\BetCombination;
use App\Models\User;

use Illuminate\Http\Request;

class BetHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $singleBets = Bet::where('user_id', $user->id)
            ->where('bet_type', 'SINGLE')
            ->get();

        $comboBets = BetCombination::where('user_id', $user->id)
            ->with('bets')
            ->get()
            ->map(function ($combination) use ($user) {
                return [
                    'combination_id' => $combination->id,
                    'status' => $combination->status,
                    'bets' => Bet::where('user_id', $user->id)
                        ->where('bet_combination_id', $combination->id)
                        ->get()
                        ->map(function ($bet) {
                            return [
                                'bet_id' => $bet->id,
                                'event_id' => $bet->event_id,
                                'outcome' => $bet->outcome,
                                'bet_amount' => $bet->bet_amount,
                                'potential_payout' => $bet->potential_payout,
                                'status' => $bet->status,
                            ];
                        }),
                ];
            });

        $betHistory = [
            'single_bets' => $singleBets,
            'combo_bets' => $comboBets,
        ];

        return response()->json($betHistory);
    }
}
