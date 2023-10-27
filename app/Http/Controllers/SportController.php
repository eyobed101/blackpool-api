<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Promise\Promise;
use Carbon\Carbon;


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
            'query' => [
                'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
            ]
        ]);
    }

    public function getGames(tRequest $request)
    {
        $sport = $request->input('sport');
        $region = $request->input('region');
        $market = $request->input('market');



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
            return $this->client->get('/v4/sports/baseball_mlb/odds', [
                'query' => [
                    'markets' => 'h2h,spreads,totals',
                    'regions' => 'us',
                    'oddsFormat' => 'american',
                    'bookmakers' => 'fanduel',
                ]
            ]);
        };

        $getNCAABasketball = function () {
            $response = $this->client->get('/v4/sports/basketball_ncaab/odds', [
                'query' => [
                    'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                    'markets' => 'h2h',
                    'regions' => 'us',
                    'oddsFormat' => 'american',
                    'bookmakers' => 'fanduel',
                ]
            ]);


            return $response->getBody()->getContents();

        };

        $getNBA = function () {
            $response = $this->client->get('/v4/sports/basketball_nba/odds', [
                'query' => [
                    'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                    'markets' => 'h2h',
                    'regions' => 'us',
                    'oddsFormat' => 'american',
                    'bookmakers' => 'draftkings',
                ]
            ]);
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

            $ncaabasketballData = Cache::remember(
                'ncaabasketball_data',
                3600,
                function () {
                    $response = $this->client->get('/v4/sports/basketball_ncaab/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    $headers = $response->getHeaders();

                    $jsonHeaders = [];

                    foreach ($headers as $name => $values) {
                        $jsonHeaders[$name] = $values[0];
                    }

                    $jsonHeaders = json_encode($jsonHeaders);

                    echo $jsonHeaders;

                  
                    return json_decode($contents, true);
                }
            );
            $nbaData = Cache::remember('nba_data', 3600, function () {
                $response = $this->client->get('/v4/sports/basketball_nba/odds', [
                    'query' => [
                        'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                        'markets' => 'h2h',
                        'regions' => 'us',
                        'oddsFormat' => 'american',
                        'bookmakers' => 'draftkings',
                    ]
                ]);



                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            
            

            $ncaabasketballDataArray = $ncaabasketballData;
            $filteredNcaabasketballData = array_filter($ncaabasketballDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });


            $nbaDataArray = $nbaData;
            $filteredNbaData = array_filter($nbaDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            

            echo 'filtered data from Nba Live'. count($filteredNbaData);


            $ncaabasketballLiveGameIds = array_column($filteredNcaabasketballData, 'id');

            foreach ($ncaabasketballLiveGameIds as $eventId) {

                $ncaabasketballLiveGameCacheTime = Cache::get('ncaabasketball_live_game_cache_time_' . $eventId);

                if (time() - $ncaabasketballLiveGameCacheTime >= 40) {

                    echo "Expired Live Game detected in Ncaab";


                    $ncaabasketballResponse = $this->client->get("/v4/sports/basketball_ncaab/events/{$eventId}/odds", [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $updatedNcaabasketballOdds = $ncaabasketballResponse->getBody()->getContents();

                    Cache::put('ncaabasketball_live_game_cache_time_' . $eventId, time(), 40);


                    foreach ($filteredNcaabasketballData as $game) {
                        if ($game['eventId'] == $eventId) {
                            $game = $updatedNcaabasketballOdds;
                            break;
                        }
                    }
                    $ncaabasketballData = array_merge($ncaabasketballDataArray, $filteredNcaabasketballData);
                    Cache::put('ncaabasketball_data', $ncaabasketballData, 120);

                }
            }


            $nbaLiveGameIds = array_column($filteredNbaData, 'id');
            foreach ($nbaLiveGameIds as $eventId) {

                $nbaLiveGameCacheTime = Cache::get('nba_live_game_cache_time_' . $eventId);

                if (time() - $nbaLiveGameCacheTime >= 40) {

                    echo "Expired Live Game detected in NBA";
                    $nbaResponse = $this->client->get("/v4/sports/basketball_nba/events/{$eventId}/odds", [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $updatedNbaOdds = $nbaResponse->getBody()->getContents();

                    Cache::put('nba_live_game_cache_time_' . $eventId, time(), 40);

                    foreach ($filteredNbaData as &$game) {
                        if ($game['id'] == $eventId) {
                            $game = $updatedNbaOdds;
                        }
                    }
                    $nbaData = array_merge($nbaDataArray, $filteredNbaData);
                    Cache::put('ncaabasketball_data', $nbaData, 120);
                }
            }
            

            

            $responseArray = Utils::all([$ncaabasketballData, $nbaData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;



        } elseif ($sport == 'football') {

            $promises = [
                Cache::remember('ncaaf_americanfootball_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/americanfootball_ncaaf/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                }),
                Cache::remember('nfl_americanfootball_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/americanfootball_nfl/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('cfl_americanfootball_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/americanfootball_cfl/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
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



            return $result;


        } elseif ($sport == 'upcoming') {

            $promises = [
                Cache::remember('ncaaf_americanfootball_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/upcoming/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
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



            return $result;


        } elseif ($sport == 'cricket') {


            $promises = [
                Cache::remember('ipl_cricket_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/cricket_ipl/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
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



            return $result;

        } elseif ($sport == 'tennis') {


            $promises = [
                Cache::remember('tennis_atp_french_open_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/tennis_atp_french_open/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                }),
                Cache::remember('tennis_atp_aus_open_singles_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/tennis_atp_aus_open_singles/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
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



            return $result;

        } elseif ($sport == 'golf') {


            $promises = [
                Cache::remember('golf_pga_championship_winner_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/golf_pga_championship_winner/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
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



            return $result;

        } elseif ($sport == 'baseball') {

            $promises = [
                Cache::remember('baseball_mlb_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/baseball_mlb/odds', [
                        'query' => [
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
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



            return $result;

        } elseif ($sport == 'soccer') {


            $promises = [
                Cache::remember('soccer_epl_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_epl/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return $contents;
                }),
                Cache::remember('soccer_england_efl_cup_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_england_efl_cup/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_uefa_champs_league_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_uefa_champs_league/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_efl_champ_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_efl_champ/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_germany_bundesliga_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_germany_bundesliga/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);
                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_spain_la_liga_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_spain_la_liga/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'american',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_fa_cup_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_fa_cup/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_brazil_campeonato_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_brazil_campeonato/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_turkey_super_league_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_turkey_super_league/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_england_league1_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_england_league1/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_australia_aleague_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_australia_aleague/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);
                    $contents = $response->getBody()->getContents();

                    return $contents;
                }),
                Cache::remember('soccer_china_superleague_data', 3600, function () {
                    $response = $this->client->get('/v4/sports/soccer_china_superleague/odds', [
                        'query' => [
                            'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
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



            return $result;

        } else {
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