<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Promise\Promise;


use Illuminate\Http\Request;

class SportController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.the-odds-api.com',
            'headers' => [
                'Accept' => 'application/json',
            ],
            'query' =>[
                'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
            ]
        ]);
    }

    public function getGames(Request $request)
    {


        $leagueRelations = [
            'MLB' => 'Baseball',
            'NCAA Basketball' => 'Basketball',
            'NBA' => 'Basketball',
            'NCAAF' => 'Football',
            'NFL' => 'Football',
            'CFL' => 'Football',
            'NHL' => 'Hockey',
            'PGA Championship Winner' => 'Golf',
            'IPL' => 'Cricket',
            'Pakistan Super League' => 'Cricket',
            'ATP Australian Open' => 'Tennis',
            'WTA US Open' => 'Tennis',
            'WTA French Open' => 'Tennis',
            'EPL' => 'Soccer',
            'UEFA Champions League' => 'Soccer',
            'Championship' => 'Soccer',
            'Bundesliga - Germany' => 'Soccer',
            'La Liga - Spain' => 'Soccer',
            'FA Cup' => 'Soccer',
            'Brazil Série A' => 'Soccer',
            'Turkey Super League' => 'Soccer',
            'Ligue 1' => 'Soccer',
            'A-League' => 'Soccer',
            'Super League - China' => 'Soccer',
        ];

        $getMLB = function () {
            return $this->client->get('/v4/sports/baseball_mlb/odds',['query' => [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]]);
        };

        $getNCAABasketball = function () {
            $response = $this->client->get('/v4/sports/basketball_ncaab/odds',['query' => [
                'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]]);
            return $response->getBody()->getContents();

        };

        $getNBA = function () {
            $response = $this->client->get('/v4/sports/basketball_nba/odds',['query' => [
                'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'draftkings',
            ]]);
            return $response->getBody()->getContents();
        };



        $getNCAAFootball = function () {
            return $this->client->get('/v4/sports/americanfootball_ncaaf/odds', [
                'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getNFL = function () {
            return $this->client->get('/v4/sports/americanfootball_nfl/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getCFL = function () {
            return $this->client->get('/v4/sports/americanfootball_cfl/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getNHL = function () {
            return $this->client->get('/v4/sports/icehockey_nhl/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getPGACWinner = function () {
            return $this->client->get('/v4/sports/golf_pga_championship_winner/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getIPL = function () {
            return $this->client->get('/v4/sports/cricket_ipl/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getATPAOpen = function () {
            return $this->client->get('/v4/sports/tennis_atp_aus_open_singles/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getWTAFOpen = function () {
            return $this->client->get('/v4/sports/tennis_atp_french_open/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        $getEPL = function () {
            return $this->client->get('/v4/sports/soccer_epl/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };
        $getEFLC = function () {
            return $this->client->get('/v4/sports/soccer_england_efl_cup/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getUEFACLeague = function () {
            return $this->client->get('/v4/sports/soccer_uefa_champs_league/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        $getChampionship = function () {
            return $this->client->get('/v4/sports/soccer_efl_champ/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getGBundesliga = function () {
            return $this->client->get('/v4/sports/soccer_germany_bundesliga/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getLaSpain = function () {
            return $this->client->get('/v4/sports/soccer_spain_la_liga/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getFACup = function () {
            return $this->client->get('/v4/sports/soccer_fa_cup/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getBrazilSA = function () {
            return $this->client->get('/v4/sports/soccer_brazil_campeonato/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getTurkeyLeague = function () {
            return $this->client->get('/v4/sports/soccer_turkey_super_league/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };


        $getLigue1 = function () {
            return $this->client->get('/v4/sports/soccer_england_league1/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getALeague = function () {
            return $this->client->get('/v4/sports/soccer_australia_aleague/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getSuperChina = function () {
            return $this->client->get('/v4/sports/soccer_china_superleague/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'decimal',
                'bookmakers' => 'fanduel',
            ]);
        };


        $sport = $request->header('sport');

        if ($sport == 'bascketball') {

            $promises = [
                $getNCAABasketball(),
                $getNBA()

            ];

            $responseArray = Cache::remember('bascketball_array', 3600, function () use ($promises) {
                try {

                    $results = Utils::all($promises)->wait();
                    // $results = Utils::settle( $promises );

                    return $results;
                    
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return ['error' => $errorMessage];
                }
            });
            $result = $responseArray;
            // echo "hhhh: " . $result ;

            return json_encode($result, true);



        } elseif ($sport == 'football') {

            $promises = [
                $getNCAAFootball(),
                $getNFL(),
                $getCFL(),

            ];

            $responseArray = Cache::remember('football_array', 3600, function () use ($promises) {
                try {
                    $results = Utils::unwrap($promises);
                    return json_encode($results);

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return ['error' => $errorMessage];
                }
            });

            $result = json_decode($responseArray, true);

            return response()->json($result);


        }
        elseif ($sport == 'cricket') {
            $promises = [
                $getIPL(),
                

            ];

            $responseArray = Cache::remember('cricket_array', 3600, function () use ($promises) {
                try {
                    $results = Utils::unwrap($promises);
                    return json_encode($results);

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return ['error' => $errorMessage];
                }
            });

            $result = json_decode($responseArray, true);

            return response()->json($result);

        }
        elseif ($sport == 'tennis') {
            $promises = [
                $getWTAFOpen(),
                $getATPAOpen(),
                

            ];

            $responseArray = Cache::remember('tennis_array', 3600, function () use ($promises) {
                try {
                    $results = Utils::unwrap($promises);
                    return json_encode($results);

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return ['error' => $errorMessage];
                }
            });

            $result = json_decode($responseArray, true);

            return response()->json($result);

        }
        elseif ($sport == 'golf') {
            $promises = [
                $getPGACWinner()
                

            ];

            $responseArray = Cache::remember('golf_array', 3600, function () use ($promises) {
                try {
                    $results = Utils::unwrap($promises);
                    return json_encode($results);

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return ['error' => $errorMessage];
                }
            });

            $result = json_decode($responseArray, true);

            return response()->json($result);

        }
        elseif ($sport == 'baseball') {
            $promises = [
                $getMLB()
                

            ];

            $responseArray = Cache::remember('baseball_array', 3600, function () use ($promises) {
                try {
                    $results = Utils::unwrap($promises);
                    return json_encode($results);

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return ['error' => $errorMessage];
                }
            });

            $result = json_decode($responseArray, true);

            return response()->json($result);

        }
        elseif ($sport == 'soccer') {
            $promises = [
                $getEPL(),
                $getEFLC(),
                $getUEFACLeague(),
                $getChampionship(),
                $getGBundesliga(),
                $getLaSpain(),
                $getFACup(),
                $getBrazilSA(),
                $getTurkeyLeague(),
                $getLigue1(),
                $getALeague(),
                $getSuperChina(),
                

            ];

            $responseArray = Cache::remember('soccer_array', 3600, function () use ($promises) {
                try {
                    $results = Utils::unwrap($promises);
                    return json_encode($results);

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    return ['error' => $errorMessage];
                }
            });

            $result = json_decode($responseArray, true);

            return response()->json($result);

        }else {
            echo "nothing is happening";
        }
    }
    public function getScores()
    {
        $scores = Cache::remember('recent_scores', 3600, function () {
            $response = $this->client->get('/v4/sports/scores');

            return json_decode($response->getBody(), true);
        });


        // return view('scores', compact('scores'));
        return response()->json($scores);

    }
}