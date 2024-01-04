<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\BetCombination;
use App\Models\User;
// use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;



class BetController extends Controller
{
    public function placeBet(Request $request)
    {

        $user = auth()->user();

       
        $user = User::find($user->id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        try {
            $jsonData = $request->getContent();
            $data = json_decode($jsonData, true);

            if ($data !== null && isset($data['data']) && isset($data['betAmount'])) {
                $betData = $data['data'];
                $isCombo = $data['is_combo_bet'];
                $selectedPrice = $data['betAmount'];

                // echo $selectedPrice;



                if ($user->balance < $selectedPrice) {
                    return response()->json(['message' => 'Insufficient balance to place the bet'], 400);
                }


                if ($isCombo) {
                    $betCombination = BetCombination::firstOrCreate([
                        "id" => Str::random(24),
                        "user_id" => $user->id,
                        "status" => 'PROCESSING'
                    ]);

                    $sportData = collect($betData)->groupBy('sport');

                    $bets = [];

                    foreach ($sportData as $sportType => $data) {
                        $request = Request::create('/api/games', 'GET');
                        $request->headers->set('sport', $sportType);

                        $response = app()->handle($request);

                        $responseData = json_decode($response->getContent(), true);

                        // echo $sportType . "   " . $responseData;

                        foreach ($data as $event) {
                            $eventId = $event['id'];
                            $eventType = $event['type'];
                            $eventTeamKey = $eventType === 'draw' ? 'Draw' : ($eventType === 'home' ? 'home_team' : 'away_team');

                            $outcome = null;

                            if ($eventType === 'home') {
                                $outcome = "HOME_TEAM_WIN";
                            } elseif ($eventType === 'away') {
                                $outcome = "AWAY_TEAM_WIN";

                            } elseif ($eventType === 'draw') {
                                $outcome = "DRAW";

                            } else {
                                $outcome = null;
                            }
                            // Find the event in the response data by ID
                            // $foundEvent = collect($responseData)->firstWhere('id', $eventId);
                            $foundEvent = collect($responseData)->firstWhere('id', $eventId);

                            if ($foundEvent) {
                                $outcomes = $foundEvent['bookmakers'][0]['markets'][0]['outcomes'] ?? [];

                                // Find the outcomes for home, draw, and away
                                $homeTeamOutcome = collect($outcomes)->firstWhere('name', $eventTeamKey === 'Draw' ? 'Draw' : $foundEvent[$eventTeamKey]);
                                $drawOutcome = collect($outcomes)->firstWhere('name', 'Draw');
                                $awayTeamOutcome = collect($outcomes)->firstWhere('name', $eventTeamKey === 'Draw' ? null : $foundEvent[$eventTeamKey]);

                                $price = null;


                                // Retrieve the price based on the event type (home, away, or draw)
                                if ($eventType === 'draw' && $drawOutcome) {
                                    $price = $drawOutcome['price'];
                                    // $prices[] = $price;

                                    $bet = Bet::firstOrCreate([
                                        'id' => Str::random(24),
                                        "user_id" => $user->id,
                                        "bet_combination_id" => $betCombination->id,
                                        "bet_type" => 'COMBO',
                                        "event_id" => $eventId,
                                        "outcome" => $outcome,
                                        "bet_amount" => $price,
                                        "potential_payout" => $price * $selectedPrice,
                                        "status" => 'PROCESSING',
                                    ]);

                                    $bets[] = $bet;
                                } elseif ($homeTeamOutcome || $awayTeamOutcome) {
                                    $price = ($eventType === 'home' ? $homeTeamOutcome : $awayTeamOutcome)['price'];
                                    // $prices[] = $price;
                                    $bet = Bet::firstOrCreate([
                                        'id' => Str::random(24),
                                        "user_id" => $user->id,
                                        "bet_combination_id" => $betCombination->id,
                                        "bet_type" => 'COMBO',
                                        "event_id" => $eventId,
                                        "outcome" => $outcome,
                                        "bet_amount" => $price,
                                        "potential_payout" => $price * $selectedPrice,
                                        "status" => 'PROCESSING',
                                    ]);

                                    $bets[] = $bet;


                                }
                            } else {
                                ;// echo "No" . "  ";
                            }


                        }




                    }
                   


                } else {

                    $datas = $betData[0];


                    $eventId = $datas['id'];
                    $eventType = $datas['type'];
                    $sportType = $datas['sport'];
                    $eventTeamKey = $eventType === 'draw' ? 'Draw' : ($eventType === 'home' ? 'home_team' : 'away_team');

                    // echo $eventId;

                    $request = Request::create('/api/games', 'GET');
                    $request->headers->set('sport', $sportType);

                    $response = app()->handle($request);

                    $responseData = json_decode($response->getContent(), true);

                     
                    // return $responseData;
                    $outcome = null;

                    if ($eventType === 'home') {
                        $outcome = "HOME_TEAM_WIN";
                    } elseif ($eventType === 'away') {
                        $outcome = "AWAY_TEAM_WIN";

                    } elseif ($eventType === 'draw') {
                        $outcome = "DRAW";

                    } else {
                        $outcome = null;
                    }

                    $foundEvent = collect($responseData)->firstWhere('id', $eventId);

                    if ($foundEvent) {


                        $outcomes = $foundEvent['bookmakers'][0]['markets'][0]['outcomes'] ?? [];

                        // Find the outcomes for home, draw, and away
                        $homeTeamOutcome = collect($outcomes)->firstWhere('name', $eventTeamKey === 'Draw' ? 'Draw' : $foundEvent[$eventTeamKey]);
                        $drawOutcome = collect($outcomes)->firstWhere('name', 'Draw');
                        $awayTeamOutcome = collect($outcomes)->firstWhere('name', $eventTeamKey === 'Draw' ? null : $foundEvent[$eventTeamKey]);

                        $price = null;


                        // Retrieve the price based on the event type (home, away, or draw)
                        if ($eventType === 'draw' && $drawOutcome) {
                            $price = $drawOutcome['price'];
                            // $prices[] = $price;

                            $bet = Bet::firstOrCreate([
                                'id' => Str::random(24),
                                "user_id" => $user->id,
                                "bet_type" => 'SINGLE',
                                "event_id" => $eventId,
                                "outcome" => $outcome,
                                "bet_amount" => $price,
                                "potential_payout" => $price * $selectedPrice,
                                "status" => 'PROCESSING',
                            ]);

                        } elseif ($homeTeamOutcome || $awayTeamOutcome) {
                            $price = ($eventType === 'home' ? $homeTeamOutcome : $awayTeamOutcome)['price'];
                            // $prices[] = $price;
                            $bet = Bet::firstOrCreate([
                                'id' => Str::random(24),
                                "user_id" => $user->id,
                                "bet_type" => 'SINGLE',
                                "event_id" => $eventId,
                                "outcome" => $outcome,
                                "bet_amount" => $price,
                                "potential_payout" => $price * $selectedPrice,
                                "status" => 'PROCESSING',
                            ]);
                        }

                    }
              
                }

                User::where('id', $user->id)->decrement('balance', $selectedPrice);


                return response()->json(['message' => 'Bet placed successfully'], 200);
            }
        } catch (\Exception $e) {
            // Log::error($e->getMessage());

            // Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}

