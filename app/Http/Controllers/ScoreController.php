<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Promise\Promise;
use App\Models\Scores;
use Carbon\Carbon;

class ScoreController extends Controller
{
    //
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.the-odds-api.com',
            'headers' => [
                'Accept' => 'application/json',
            ],

        ]);
    }

    public function getScores(Request $request)
    {


        $sport = $request->header('sport');

        if ($sport == 'bascketball') {
            $promises = [
                Cache::remember('ncaabasketball_score_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/basketball_ncaab/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                }),


                Cache::remember('nba_score_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/basketball_nba/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
            ];

            $responseArray = Utils::all($promises)->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = json_decode($file);


                $result = array_merge($result, $decodedFile);

            }
            foreach ($result as $file) {
                if ($file->completed) {
                    $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                    $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                    Scores::firstOrCreate([
                        'id' => $file->id,
                        'sport_key' => $file->sport_key,
                        'commence_time' => $commenceTime,
                        'home_team' => $file->home_team,
                        'away_team' => $file->away_team,
                    ], [
                        'sport_title' => $file->sport_title,
                        'completed' => $file->completed,
                        'scores' => json_encode($file->scores),
                        'last_update' => $lastUpdate,
                    ]);


                }
            }
            return $result;



        } elseif ($sport == 'football') {

            $promises = [
                Cache::remember('ncaaf_americanfootball_score_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/americanfootball_ncaaf/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                }),
                Cache::remember('nfl_americanfootball_score_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/americanfootball_nfl/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('cfl_americanfootball_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/americanfootball_cfl/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
            ];

            $responseArray = Utils::all($promises)->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = json_decode($file);
                $result = array_merge($result, $decodedFile);
            }

            foreach ($result as $file) {
                if ($file->completed) {
                    $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                    $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                    Scores::firstOrCreate([
                        'id' => $file->id,
                        'sport_key' => $file->sport_key,
                        'commence_time' => $commenceTime,
                        'home_team' => $file->home_team,
                        'away_team' => $file->away_team,
                    ], [
                        'sport_title' => $file->sport_title,
                        'completed' => $file->completed,
                        'scores' => json_encode($file->scores),
                        'last_update' => $lastUpdate,
                    ]);


                }
            }



            return $result;


        } elseif ($sport == 'cricket') {


            $promises = [
                Cache::remember('ipl_cricket_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/cricket_ipl/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                })
            ];

            $responseArray = Utils::all($promises)->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = json_decode($file);
                $result = array_merge($result, $decodedFile);
            }

            foreach ($result as $file) {
                if ($file->completed) {
                    $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                    $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                    Scores::firstOrCreate([
                        'id' => $file->id,
                        'sport_key' => $file->sport_key,
                        'commence_time' => $commenceTime,
                        'home_team' => $file->home_team,
                        'away_team' => $file->away_team,
                    ], [
                        'sport_title' => $file->sport_title,
                        'completed' => $file->completed,
                        'scores' => json_encode($file->scores),
                        'last_update' => $lastUpdate,
                    ]);


                }
            }

            return $result;

        } elseif ($sport == 'tennis') {


            $promises = [
                Cache::remember('tennis_atp_french_open_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/tennis_atp_french_open/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                }),
                Cache::remember('tennis_atp_aus_open_singles_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/tennis_atp_aus_open_singles/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
            ];

            $responseArray = Utils::all($promises)->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = json_decode($file);
                $result = array_merge($result, $decodedFile);
            }

            foreach ($result as $file) {
                if ($file->completed) {
                    $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                    $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                    Scores::firstOrCreate([
                        'id' => $file->id,
                        'sport_key' => $file->sport_key,
                        'commence_time' => $commenceTime,
                        'home_team' => $file->home_team,
                        'away_team' => $file->away_team,
                    ], [
                        'sport_title' => $file->sport_title,
                        'completed' => $file->completed,
                        'scores' => json_encode($file->scores),
                        'last_update' => $lastUpdate,
                    ]);


                }
            }

            return $result;

        } elseif ($sport == 'golf') {


            $promises = [
                Cache::remember('golf_pga_championship_winner_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/golf_pga_championship_winner/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                })
            ];

            $responseArray = Utils::all($promises)->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = json_decode($file);
                $result = array_merge($result, $decodedFile);
            }

            foreach ($result as $file) {
                if ($file->completed) {
                    $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                    $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                    Scores::firstOrCreate([
                        'id' => $file->id,
                        'sport_key' => $file->sport_key,
                        'commence_time' => $commenceTime,
                        'home_team' => $file->home_team,
                        'away_team' => $file->away_team,
                    ], [
                        'sport_title' => $file->sport_title,
                        'completed' => $file->completed,
                        'scores' => json_encode($file->scores),
                        'last_update' => $lastUpdate,
                    ]);


                }
            }

            return $result;

        } elseif ($sport == 'baseball') {

            $promises = [
                Cache::remember('baseball_mlb_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/baseball_mlb/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                })
            ];

            $responseArray = Utils::all($promises)->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = json_decode($file);
                $result = array_merge($result, $decodedFile);
            }

            foreach ($result as $file) {
                if ($file->completed) {
                    $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                    $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                    Scores::firstOrCreate([
                        'id' => $file->id,
                        'sport_key' => $file->sport_key,
                        'commence_time' => $commenceTime,
                        'home_team' => $file->home_team,
                        'away_team' => $file->away_team,
                    ], [
                        'sport_title' => $file->sport_title,
                        'completed' => $file->completed,
                        'scores' => json_encode($file->scores),
                        'last_update' => $lastUpdate,
                    ]);


                }
            }

            return $result;

        } elseif ($sport == 'soccer') {


            $promises = [
                Cache::remember('soccer_epl_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_epl/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                }),
                Cache::remember('soccer_england_efl_cup_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_england_efl_cup/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_uefa_champs_league_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_uefa_champs_league/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_efl_champ_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_efl_champ/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_germany_bundesliga_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_germany_bundesliga/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);
                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_spain_la_liga_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_spain_la_liga/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_fa_cup_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_fa_cup/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_brazil_campeonato_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_brazil_campeonato/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_turkey_super_league_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_turkey_super_league/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_england_league1_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_england_league1/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_australia_aleague_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_australia_aleague/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);
                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_china_superleague_score_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_china_superleague/scores', [
                        'query' => [
                            'apiKey' =>  env('API_KEY'),
                            'daysFrom' => '1',

                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
            ];

            $responseArray = Utils::all($promises)->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = json_decode($file);
                $result = array_merge($result, $decodedFile);
            }

            foreach ($result as $file) {
                if ($file->completed) {
                    $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                    $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                    Scores::firstOrCreate([
                        'id' => $file->id,
                        'sport_key' => $file->sport_key,
                        'commence_time' => $commenceTime,
                        'home_team' => $file->home_team,
                        'away_team' => $file->away_team,
                    ], [
                        'sport_title' => $file->sport_title,
                        'completed' => $file->completed,
                        'scores' => json_encode($file->scores),
                        'last_update' => $lastUpdate,
                    ]);


                }
            }


            return $result;

        } else {
            echo "nothing is happening";
        }
    }

    public function getScoresScheduled(){

        $promises = [
            Cache::remember('ncaabasketball_score_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/basketball_ncaab/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();
                return $contents;
            }),


            Cache::remember('nba_score_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/basketball_nba/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('ncaaf_americanfootball_score_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/americanfootball_ncaaf/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();
                return $contents;
            }),
            Cache::remember('nfl_americanfootball_score_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/americanfootball_nfl/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('cfl_americanfootball_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/americanfootball_cfl/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('ipl_cricket_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/cricket_ipl/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();
                return $contents;
            }),
            Cache::remember('tennis_atp_french_open_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/tennis_atp_french_open/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();
                return $contents;
            }),
            Cache::remember('tennis_atp_aus_open_singles_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/tennis_atp_aus_open_singles/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('golf_pga_championship_winner_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/golf_pga_championship_winner/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();
                return $contents;
            }),
            Cache::remember('baseball_mlb_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/baseball_mlb/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();
                return $contents;
            }),
            Cache::remember('soccer_epl_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_epl/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();
                return $contents;
            }),
            Cache::remember('soccer_england_efl_cup_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_england_efl_cup/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_uefa_champs_league_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_uefa_champs_league/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_efl_champ_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_efl_champ/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_germany_bundesliga_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_germany_bundesliga/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);
                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_spain_la_liga_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_spain_la_liga/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_fa_cup_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_fa_cup/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_brazil_campeonato_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_brazil_campeonato/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_turkey_super_league_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_turkey_super_league/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_england_league1_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_england_league1/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_australia_aleague_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_australia_aleague/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);
                $contents = $response->getBody()->getContents();

                return $contents;
            }),
            Cache::remember('soccer_china_superleague_score_data', 3600, function () {
                $response = $this->client->get('/v4/sports/soccer_china_superleague/scores', [
                    'query' => [
                        'apiKey' =>  env('API_KEY'),
                        'daysFrom' => '1',

                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return $contents;
            }),
        ];

        $responseArray = Utils::all($promises)->wait();

        $result = [];

        foreach ($responseArray as $file) {

            $decodedFile = json_decode($file);


            $result = array_merge($result, $decodedFile);

        }
        foreach ($result as $file) {
            if ($file->completed) {
                $commenceTime = Carbon::parse($file->commence_time)->toDateTimeString();
                $lastUpdate = Carbon::parse($file->last_update)->toDateTimeString();
                Scores::firstOrCreate([
                    'id' => $file->id,
                    'sport_key' => $file->sport_key,
                    'commence_time' => $commenceTime,
                    'home_team' => $file->home_team,
                    'away_team' => $file->away_team,
                ], [
                    'sport_title' => $file->sport_title,
                    'completed' => $file->completed,
                    'scores' => json_encode($file->scores),
                    'last_update' => $lastUpdate,
                ]);


            }
        }

    }
}