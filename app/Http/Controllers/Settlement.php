<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\BetCombination;
use App\Models\Scores;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class Settlement extends Controller
{
    public function checkBetOutcome(Request $request)
    {
        $user = auth()->user();

        try {
            $this->processSingleBets($user);

            $this->processBetCombinations($user);

            return response()->json(['message' => 'Bet settled successfully', 'user' => $user]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json($e->getMessage(), 500);
        }
    }

    private function processSingleBets($user)
    {
        $singleBets = Bet::where('user_id', $user->id)
            ->where('bet_type', 'SINGLE')
            ->where('status', 'PROCESSING')
            ->get();

        if ($singleBets->isNotEmpty()) {
            foreach ($singleBets as $bet) {
                $this->processBet($bet, $user);
            }
        }
    }

    private function processBetCombinations($user)
{
    $betCombinations = BetCombination::where('user_id', $user->id)
        ->where('status', 'PROCESSING')
        ->get();

    if ($betCombinations->isNotEmpty()) {
        foreach ($betCombinations as $betCombination) {
            $bets = Bet::where('bet_combination_id', $betCombination->id)->get();
            $allScoresFound = true;
            $allBetsCompleted = true; 
            $anyWrongGuess = false;
            $anyCorrectGuess = false;
            $potentialPayouts = [];

            foreach ($bets as $bet) {
                $score = Scores::find($bet->event_id);
                if (!$score) {
                    $allScoresFound = false;
                    continue;
                }

                $outcome = $this->getBetOutcome($bet, $score);

                if (!$outcome) {
                    $anyWrongGuess = true;
                    $allBetsCompleted = false; 
                } else {
                    $anyCorrectGuess = true;
                }

                $potentialPayouts[] = $bet->potential_payout;
            }

            if (!$allScoresFound) {
                $betCombination->status = 'PROCESSING';
                $betCombination->save();
            } elseif ($anyWrongGuess) {
                $this->markBetsAndCombinationFailed($bets, $betCombination);
            } elseif ($allBetsCompleted && $anyCorrectGuess) {
                $this->markBetsAndCombinationCompleted($bets, $betCombination, $user, $potentialPayouts);
            } else {
                $this->markBetsAndCombinationProcessing($bets, $betCombination);
            }
        }
    }
}


    private function processBet($bet, $user)
    {
        $eventId = $bet->event_id;
        $score = Scores::find($eventId);

        if ($score) {
            $outcome = $this->getBetOutcome($bet, $score);
            if ($outcome) {
                $bet->status = 'COMPLETED';
                $bet->save();
                User::where('id', $user->id)->increment('balance', $bet->potential_payout);
            } else {
                $bet->status = 'FAILED';
                $bet->save();
            }
        } else {
            $bet->status = 'PROCESSING';
            $bet->save();
        }
    }

    private function getBetOutcome($bet, $score)
    {
        $selectedOutcome = $bet->outcome;
        
        $data = json_decode($score->scores, true);
        $home_team = $score->home_team;
        $away_team = $score->away_team;

        $homeTeamScore = null;
        $awayTeamScore = null;

        foreach ($data as $teamScore) {
            if ($teamScore['name'] === $home_team) {
                $homeTeamScore = $teamScore['score'];
            } elseif ($teamScore['name'] === $away_team) {
                $awayTeamScore = $teamScore['score'];
            }
        }

        switch ($selectedOutcome) {
            case 'HOME_TEAM_WIN':
                return ($homeTeamScore > $awayTeamScore);
            case 'AWAY_TEAM_WIN':
                return ($awayTeamScore > $homeTeamScore);
            case 'DRAW':
                return ($homeTeamScore === $awayTeamScore);
            case 'OVER_GOAL':
                return (($homeTeamScore + $awayTeamScore) > 5);
            case 'UNDER_GOAL':
                return (($homeTeamScore + $awayTeamScore) < 5);
            default:
                return false;
        }
    }

    private function markBetsAndCombinationFailed($bets, $betCombination)
    {
        foreach ($bets as $bet) {
            $bet->status = 'FAILED';
            $bet->save();
        }
        $betCombination->status = 'FAILED';
        $betCombination->save();
    }

    private function markBetsAndCombinationProcessing($bets, $betCombination)
    {
        foreach ($bets as $bet) {
            $bet->status = 'PROCESSING';
            $bet->save();
        }
        $betCombination->status = 'PROCESSING';
        $betCombination->save();
    }

    private function markBetsAndCombinationCompleted($bets, $betCombination, $user, $potentialPayouts)
    {
        foreach ($bets as $bet) {
            $bet->status = 'COMPLETED';
            $bet->save();
        }
        $betCombination->status = 'COMPLETED';
        $betCombination->save();
        User::where('id', $user->id)->increment('balance', array_sum($potentialPayouts));
    }
}
