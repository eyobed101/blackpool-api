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
    //
    public function checkBetOutcome(Request $request)
    {

        $user = auth()->user();





        try {
            $singleBets = Bet::where('user_id', $user->id)
                ->where('bet_type', 'SINGLE')
                ->where('status', 'PROCESSING')
                ->get();


            if ($singleBets->isNotEmpty()) {
                $preAgreedGoal = 5;


                foreach ($singleBets as $bet) {
                    $eventId = $bet->event_id;

                    $score = Scores::find($eventId);

                    if ($score) {


                        $outcome = $score->outcome;
                        $selectedOutcomes = $bet->outcome;
                        $potentialPayouts = $bet->potential_payout;
                        $selectedOutcome = $selectedOutcomes[$eventId];

                        $allWon = true;

                        $data = json_decode($score->scores, true);

                        $home_team = $score["home_team"];
                        $away_team = $score["away_team"];

                        $homeTeamScore = null;
                        $awayTeamScore = null;

                        foreach ($data as $score) {
                            if ($score['name'] === $home_team) {
                                $homeTeamScore = $score['score'];
                            } elseif ($score['name'] === $away_team) {
                                $awayTeamScore = $score['score'];

                            }
                        }


                        if ($selectedOutcome === 'HOME_TEAM_WIN' && intval($homeTeamScore) < intval($awayTeamScore)) {
                            $allWon = false;

                        } elseif ($selectedOutcome === 'AWAY_TEAM_WIN' && intval($awayTeamScore) < intval($homeTeamScore)) {
                            $allWon = false;

                        } elseif ($selectedOutcome === 'DRAW' && intval($homeTeamScore) != intval($awayTeamScore)) {
                            $allWon = false;

                        } elseif ($selectedOutcome === 'OVER_GOAL' && (intval($homeTeamScore) + intval($awayTeamScore)) <= $preAgreedGoal) {
                            $allWon = false;

                        } elseif ($selectedOutcome === 'UNDER_GOAL' && (intval($homeTeamScore) + intval($awayTeamScore)) >= $preAgreedGoal) {
                            $allWon = false;

                        }

                        if ($allWon) {
                            $bet->status = 'COMPLETED';
                            $bet->save();


                            User::where('id', $user->id)->increment('balance', $potentialPayouts);



                        } else {
                            if ($bet->status !== 'PROCESSING') {
                                $bet->status = 'FAILED';
                                $bet->save();

                            }
                        }
                    } else {
                        $bet->status = 'PROCESSING';
                    }

                    $bet->save();
                }

            }

            $betCombinations = BetCombination::where('user_id', $user->id)
                ->where('status', 'PROCESSING')
                ->get();

            if ($betCombinations->isNotEmpty()) {

                $preAgreedGoal = 5;
                foreach ($betCombinations as $betCombination) {
                    $bets = Bet::where('bet_combination_id', $betCombination->id)->get();

                    $selectedEvents = [];
                    $selectedOutcomes = [];
                    $potentialPayouts = [];

                    foreach ($bets as $bet) {

                        $selectedEvents[] = $bet->event_id;
                        $selectedOutcomes[$bet->event_id] = $bet->outcome;
                        $potentialPayouts[] = $bet->potential_payout;
                    }

                    $scores = Scores::whereIn('id', $selectedEvents)->get();

                    $allWon = true;
                    foreach ($bets as $bet) {
                        $eventId = $bet->event_id;

                        $score = $scores->firstWhere('id', $eventId);

                        if (!$score) {
                            $bet->status = 'PROCESSING';
                            $bet->save();
                            $allWon = false;
                            continue;
                        }

                        $outcome = $score->outcome;
                        $selectedOutcome = $selectedOutcomes[$eventId];


                        $data = json_decode($score->scores, true);

                        $home_team = $score["home_team"];
                        $away_team = $score["away_team"];

                        $homeTeamScore = null;
                        $awayTeamScore = null;

                        foreach ($data as $score) {
                            if ($score['name'] === $home_team) {
                                $homeTeamScore = $score['score'];
                            } elseif ($score['name'] === $away_team) {
                                $awayTeamScore = $score['score'];

                            }
                        }


                        if ($selectedOutcome === 'HOME_TEAM_WIN' && intval($homeTeamScore) < intval($awayTeamScore)) {
                            $allWon = false;
                            break;
                        } elseif ($selectedOutcome === 'AWAY_TEAM_WIN' && intval($awayTeamScore) < intval($homeTeamScore)) {
                            $allWon = false;
                            break;
                        } elseif ($selectedOutcome === 'DRAW' && intval($homeTeamScore) != intval($awayTeamScore)) {
                            $allWon = false;
                            break;
                        } elseif ($selectedOutcome === 'OVER_GOAL' && (intval($homeTeamScore) + intval($awayTeamScore)) <= $preAgreedGoal) {
                            $allWon = false;
                            break;
                        } elseif ($selectedOutcome === 'UNDER_GOAL' && (intval($homeTeamScore) + intval($awayTeamScore)) >= $preAgreedGoal) {
                            $allWon = false;
                            break;
                        }

                    }

                    if ($allWon) {
                        foreach ($bets as $bet) {
                            $bet->status = 'COMPLETED';
                            $bet->save();
                        }

                        User::where('id', $user->id)->increment('balance', array_sum($potentialPayouts));



                    } else {
                        foreach ($bets as $bet) {
                            if ($bet->status !== 'PROCESSING') {
                                $bet->status = 'FAILED';
                                $bet->save();
                            }
                        }
                    }


                }

            }

                return response()->json(['message' => 'Bet settled successfully', 'user' => $user]);




        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json($e->getMessage(), 500);
        }
        return response()->json(['message' => 'Failed to settle bet']);


    }
}