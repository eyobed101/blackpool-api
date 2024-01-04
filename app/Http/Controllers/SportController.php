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
                'apiKey' => env('API_KEY'),
            ]
        ]);
    }

    public function getGames(Request $request)
    {

        $sport = $request->header('sport');

        if ($sport == 'bascketball') {

            $ncaabasketballData = Cache::remember(
                'ncaabasketball_data',
                120,
                function () {
                    $response = $this->client->get('/v4/sports/basketball_ncaab/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                
                    return json_decode($contents, true);
                }
            );
            $nbaData = Cache::remember('nba_data', 120, function () {
                $response = $this->client->get('/v4/sports/basketball_nba/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'draftkings',
                    ]
                ]);



                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });




            $ncaabasketballDataArray = $ncaabasketballData;
            $filteredNcaabasketballData = array_filter($ncaabasketballDataArray, function ($game) {
                $current_time = Carbon::now();
                // echo  $current_time . ' vs ' . Carbon::parse($game['commence_time']) ;
                return Carbon::parse($game['commence_time']) <= $current_time;
            });
            // echo 'filtered data from Ncaaa Live' . count($filteredNcaabasketballData);


            $nbaDataArray = $nbaData;
            $filteredNbaData = array_filter($nbaDataArray, function ($game) {
                $current_time = Carbon::now();
                // echo $current_time;

                return Carbon::parse($game['commence_time']) <= $current_time;
            });



            // echo 'filtered data from Nba Live' . count($filteredNbaData);


            $ncaabasketballLiveGameIds = array_column($filteredNcaabasketballData, 'id');

            foreach ($ncaabasketballLiveGameIds as $eventId) {

                $ncaabasketballLiveGameCacheTime = Cache::get('ncaabasketball_live_game_cache_time_' . $eventId);

                if (time() - $ncaabasketballLiveGameCacheTime >= 40) {

                    try {

                        $ncaabasketballResponse = $this->client->get("/v4/sports/basketball_ncaab/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedNcaabasketballOdds = json_decode($ncaabasketballResponse->getBody()->getContents(), true);

                        Cache::put('ncaabasketball_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredNcaabasketballData as $game) {
                            if ($game['eventId'] == $eventId) {
                                $game = $updatedNcaabasketballOdds;
                                break;
                            }
                        }
                        $ncaabasketballData = array_merge($ncaabasketballDataArray, $filteredNcaabasketballData);
                        $cacheKey = 'ncaabasketball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('ncaabasketball_data', $ncaabasketballData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('ncaabasketball_live_game_cache_time_' . $eventId);
                        foreach ($ncaabasketballDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($ncaabasketballDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $ncaabasketballData = array_values($ncaabasketballDataArray);

                        $cacheKey = 'ncaabasketball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $ncaabasketballData, $remainingTime);
                    }
                }
            }


            $nbaLiveGameIds = array_column($filteredNbaData, 'id');
            foreach ($nbaLiveGameIds as $eventId) {

                $nbaLiveGameCacheTime = Cache::get('nba_live_game_cache_time_' . $eventId);

                if (time() - $nbaLiveGameCacheTime >= 40) {
                    try {

                        // echo "Expired Live Game detected in NBA";
                        $nbaResponse = $this->client->get("/v4/sports/basketball_nba/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedNbaOdds = json_decode($nbaResponse->getBody()->getContents(), true);

                        Cache::put('nba_live_game_cache_time_' . $eventId, time(), 40);

                        foreach ($filteredNbaData as &$game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedNbaOdds;
                            }
                        }
                        unset($game);
                        $nbaData = array_merge($nbaDataArray, $filteredNbaData);

                        $cacheKey = 'nba_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('nba_data', $nbaData, $remainingTime);
                    } catch (\Exception $e) {
                        Cache::forget('nba_live_game_cache_time_' . $eventId);
                        foreach ($nbaDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($nbaDataArray[$key]);
                                break;
                            }
                        }
                        $cacheKey = 'nba_data';

                        // Merge the updated array after removing the game data
                        $nbaData = array_values($nbaDataArray);
                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $nbaData, $remainingTime);
                    }
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

            $ncaafootballData =
                Cache::remember('ncaaf_americanfootball_data', 120, function () {
                    $response = $this->client->get('/v4/sports/americanfootball_ncaaf/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return json_decode($contents, true);
                });

            $nflfootballData = Cache::remember('nfl_americanfootball_data', 120, function () {
                $response = $this->client->get('/v4/sports/americanfootball_nfl/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $cflfootballData = Cache::remember('cfl_americanfootball_data', 120, function () {
                $response = $this->client->get('/v4/sports/americanfootball_cfl/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $ncaafootballDataArray = $ncaafootballData;
            $filteredNcaafootballData = array_filter($ncaafootballDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $ncaafootballLiveGameIds = array_column($filteredNcaafootballData, 'id');

            $nflfootballDataArray = $nflfootballData;
            $filteredNflfootballData = array_filter($nflfootballDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $nflfootballLiveGameIds = array_column($filteredNflfootballData, 'id');

            $cflfootballDataArray = $cflfootballData;
            $filteredCflfootballData = array_filter($cflfootballDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $cflfootballLiveGameIds = array_column($filteredCflfootballData, 'id');


            foreach ($ncaafootballLiveGameIds as $eventId) {

                $ncaafootballLiveGameCacheTime = Cache::get('ncaafootball_live_game_cache_time_' . $eventId);

                if (time() - $ncaafootballLiveGameCacheTime >= 40) {

                    try {

                        $ncaafootballResponse = $this->client->get("/v4/sports/americanfootball_ncaaf/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedNcaafootballOdds = json_decode($ncaafootballResponse->getBody()->getContents(), true);

                        Cache::put('ncaafootball_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredNcaafootballData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedNcaafootballOdds;
                                break;
                            }
                        }

                        $ncaafootballData = array_merge($ncaafootballDataArray, $filteredNcaafootballData);

                        $cacheKey = 'ncaaf_americanfootball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('ncaaf_americanfootball_data', $ncaafootballData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('ncaafootball_live_game_cache_time_' . $eventId);
                        foreach ($ncaafootballDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($ncaafootballDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $ncaafootballData = array_values($ncaafootballDataArray);

                        $cacheKey = 'ncaaf_americanfootball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $ncaafootballData, $remainingTime);
                    }
                }
            }
            foreach ($nflfootballLiveGameIds as $eventId) {

                $nflfootballLiveGameCacheTime = Cache::get('nflfootball_live_game_cache_time_' . $eventId);

                if (time() - $nflfootballLiveGameCacheTime >= 40) {


                    try {
                        $nflfootballResponse = $this->client->get("/v4/sports/americanfootball_nfl/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedNflfootballOdds = json_decode($nflfootballResponse->getBody()->getContents(), true);

                        Cache::put('nflfootball_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredNflfootballData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedNflfootballOdds;
                                break;
                            }
                        }
                        $nflfootballData = array_merge($nflfootballDataArray, $filteredNflfootballData);
                        $cacheKey = 'nfl_americanfootball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('nfl_americanfootball_data', $nflfootballData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('nflfootball_live_game_cache_time_' . $eventId);
                        foreach ($nflfootballDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($nflfootballDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $nflfootballData = array_values($nflfootballDataArray);

                        $cacheKey = 'nfl_americanfootball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $nflfootballData, $remainingTime);
                    }
                }
            }
            foreach ($cflfootballLiveGameIds as $eventId) {

                $cflfootballLiveGameCacheTime = Cache::get('cflfootball_live_game_cache_time_' . $eventId);

                if (time() - $cflfootballLiveGameCacheTime >= 40) {

                    try {

                        $cflfootballResponse = $this->client->get("/v4/sports/americanfootball_cfl/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedCflfootballOdds = json_decode($cflfootballResponse->getBody()->getContents(), true);

                        Cache::put('cflfootball_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredCflfootballData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedCflfootballOdds;
                                break;
                            }
                        }
                        $cflfootballData = array_merge($cflfootballDataArray, $filteredCflfootballData);

                        $cacheKey = 'cfl_americanfootball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('cfl_americanfootball_data', $cflfootballData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('cflfootball_live_game_cache_time_' . $eventId);
                        foreach ($cflfootballDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($cflfootballDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $cflfootballData = array_values($cflfootballDataArray);

                        $cacheKey = 'cfl_americanfootball_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $cflfootballData, $remainingTime);
                    }
                }
            }




            $responseArray = Utils::all([$ncaafootballData, $nflfootballData, $cflfootballData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;


        } elseif ($sport == 'upcoming') {

            $upcomingData =
                Cache::remember('upcoming_data', 120, function () {
                    $response = $this->client->get('/v4/sports/upcoming/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return json_decode($contents, true);
                });


            $upcomingDataArray = $upcomingData;
            $filteredUpcomingData = array_filter($upcomingDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $upcomingLiveGameIds = array_column($filteredUpcomingData, 'id');


            foreach ($upcomingLiveGameIds as $eventId) {

                $upcomingLiveGameCacheTime = Cache::get('upcoming_live_game_cache_time_' . $eventId);

                if (time() - $upcomingLiveGameCacheTime >= 40) {

                    try {

                        $upcomingResponse = $this->client->get("/v4/sports/upcoming/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedUpcomingOdds = json_decode($upcomingResponse->getBody()->getContents(), true);



                        Cache::put('upcoming_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredUpcomingData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedUpcomingOdds;
                                break;
                            }
                        }
                        $upcomingData = array_merge($upcomingDataArray, $filteredUpcomingData);
                        $cacheKey = 'upcoming_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('upcoming_data', $upcomingData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('upcoming_live_game_cache_time_' . $eventId);
                        foreach ($upcomingDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($upcomingDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $upcomingData = array_values($upcomingDataArray);

                        $cacheKey = 'upcoming_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $upcomingData, $remainingTime);
                    }
                }
            }


            $responseArray = Utils::all([$upcomingData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;


        } elseif ($sport == 'cricket') {


            $iplcricketData =
                Cache::remember('ipl_cricket_data', 120, function () {
                    $response = $this->client->get('/v4/sports/cricket_ipl/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return json_decode($contents, true);
                });

            $iplcricketDataArray = $iplcricketData;
            $filteredIplcricketData = array_filter($iplcricketDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $iplcricketLiveGameIds = array_column($filteredIplcricketData, 'id');


            foreach ($iplcricketLiveGameIds as $eventId) {

                $iplcricketLiveGameCacheTime = Cache::get('iplcricket_live_game_cache_time_' . $eventId);

                if (time() - $iplcricketLiveGameCacheTime >= 40) {

                    try {


                        $iplcricketResponse = $this->client->get("/v4/sports/cricket_ipl/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedIplcricketOdds = json_decode($iplcricketResponse->getBody()->getContents(), true);

                        Cache::put('iplcricket_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredIplcricketData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedIplcricketOdds;
                                break;
                            }
                        }
                        $iplcricketData = array_merge($iplcricketDataArray, $filteredIplcricketData);

                        $cacheKey = 'ipl_cricket_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('ipl_cricket_data', $iplcricketData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('iplcricket_live_game_cache_time_' . $eventId);
                        foreach ($iplcricketDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($iplcricketDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $iplcricketData = array_values($iplcricketDataArray);

                        $cacheKey = 'ipl_cricket_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $iplcricketData, $remainingTime);
                    }
                }
            }


            $bigbashData =
                Cache::remember('big_bash_data', 120, function () {
                    $response = $this->client->get('/v4/sports/cricket_big_bash/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'unibet_us',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return json_decode($contents, true);
                });

            $bigBashDataArray = $bigbashData;
            $filteredBigbashData = array_filter($bigBashDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $bigbashLiveGameIds = array_column($filteredBigbashData, 'id');


            foreach ($bigbashLiveGameIds as $eventId) {

                $bigbashLiveGameCacheTime = Cache::get('bigbash_live_game_cache_time_' . $eventId);

                if (time() - $bigbashLiveGameCacheTime >= 40) {

                    try {


                        $bigbashResponse = $this->client->get("/v4/sports/cricket_big_bash/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'lowvig',
                            ]
                        ]);

                        $updatedBigbashOdds = json_decode($bigbashResponse->getBody()->getContents(), true);

                        Cache::put('bigbash_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredBigbashData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedBigbashOdds;
                                break;
                            }
                        }
                        $bigbashData = array_merge($bigBashDataArray, $filteredBigbashData);

                        $cacheKey = 'big_bash_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $bigbashData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('bigbash_live_game_cache_time_' . $eventId);
                        
                        foreach ($bigBashDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($bigBashDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $bigbashData = array_values($bigBashDataArray);

                        $cacheKey = 'big_bash_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $bigbashData, $remainingTime);
                    }
                }
            }


            $responseArray = Utils::all([$iplcricketData, $bigbashData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;

        } elseif ($sport == 'tennis') {


            $frenchtennisData =
                Cache::remember('tennis_atp_french_open_data', 120, function () {
                    $response = $this->client->get('/v4/sports/tennis_atp_french_open/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();

                    return json_decode($contents, true);
                });

            $austennisData = Cache::remember('tennis_atp_aus_open_singles_data', 120, function () {
                $response = $this->client->get('/v4/sports/tennis_atp_aus_open_singles/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $frenchtennisDataArray = $frenchtennisData;
            $filteredFrenchtennisData = array_filter($frenchtennisDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $frenchtennisLiveGameIds = array_column($filteredFrenchtennisData, 'id');


            foreach ($frenchtennisLiveGameIds as $eventId) {

                $frenchtennisLiveGameCacheTime = Cache::get('frenchtennis_live_game_cache_time_' . $eventId);

                if (time() - $frenchtennisLiveGameCacheTime >= 40) {

                    try {

                        $frenchtennisResponse = $this->client->get("/v4/sports/tennis_atp_french_open/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedFrenchtennisOdds = json_decode($frenchtennisResponse->getBody()->getContents(), true);

                        Cache::put('frenchtennis_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredFrenchtennisData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedFrenchtennisOdds;
                                break;
                            }
                        }
                        $frenchtennisData = array_merge($frenchtennisDataArray, $filteredFrenchtennisData);

                        $cacheKey = 'tennis_atp_french_open_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('tennis_atp_french_open_data', $frenchtennisData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('frenchtennis_live_game_cache_time_' . $eventId);
                        foreach ($frenchtennisDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($frenchtennisDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $frenchtennisData = array_values($frenchtennisDataArray);

                        $cacheKey = 'tennis_atp_french_open_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $frenchtennisData, $remainingTime);
                    }
                }
            }

            $austennisDataArray = $austennisData;
            $filteredAustennisData = array_filter($austennisDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $austennisLiveGameIds = array_column($filteredAustennisData, 'id');


            foreach ($austennisLiveGameIds as $eventId) {

                $austennisLiveGameCacheTime = Cache::get('austennis_live_game_cache_time_' . $eventId);

                if (time() - $austennisLiveGameCacheTime >= 40) {

                    try {

                        $austennisResponse = $this->client->get("/v4/sports/tennis_atp_aus_open_singles/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedAustennisOdds = json_decode($austennisResponse->getBody()->getContents(), true);

                        Cache::put('austennis_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredAustennisData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedAustennisOdds;
                                break;
                            }
                        }
                        $austennisData = array_merge($austennisDataArray, $filteredAustennisData);

                        $cacheKey = 'tennis_atp_aus_open_singles_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('tennis_atp_aus_open_singles_data', $austennisData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('austennis_live_game_cache_time_' . $eventId);
                        foreach ($austennisDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($austennisDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $austennisData = array_values($austennisDataArray);

                        $cacheKey = 'tennis_atp_aus_open_singles_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $austennisData, $remainingTime);
                    }
                }
            }

            $responseArray = Utils::all([$frenchtennisData, $austennisData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;

        } elseif ($sport == 'golf') {


            $pgagolfData =
                Cache::remember('golf_pga_championship_winner_data', 120, function () {
                    $response = $this->client->get('/v4/sports/golf_pga_championship_winner/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return json_decode($contents, true);
                });

            $pgagolfDataArray = $pgagolfData;
            $filteredPgagolfData = array_filter($pgagolfDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $pgagolfLiveGameIds = array_column($filteredPgagolfData, 'id');


            foreach ($pgagolfLiveGameIds as $eventId) {

                $pgagolfLiveGameCacheTime = Cache::get('pgagolf_live_game_cache_time_' . $eventId);

                if (time() - $pgagolfLiveGameCacheTime >= 40) {

                    try {

                        $pgagolfResponse = $this->client->get("/v4/sports/golf_pga_championship_winner/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedPgagolfOdds = json_decode($pgagolfResponse->getBody()->getContents(), true);

                        Cache::put('pgagolf_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredPgagolfData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedPgagolfOdds;
                                break;
                            }
                        }
                        $pgagolfData = array_merge($pgagolfDataArray, $filteredPgagolfData);

                        $cacheKey = 'golf_pga_championship_winner_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('golf_pga_championship_winner_data', $pgagolfData, $remainingTime);
                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('pgagolf_live_game_cache_time_' . $eventId);
                        foreach ($pgagolfDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($pgagolfDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $pgagolfData = array_values($pgagolfDataArray);

                        $cacheKey = 'golf_pga_championship_winner_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $pgagolfData, $remainingTime);
                    }
                }
            }


            $responseArray = Utils::all([$pgagolfData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;

        } elseif ($sport == 'baseball') {

            $mlbbaseballData =
                Cache::remember('baseball_mlb_data', 120, function () {
                    $response = $this->client->get('/v4/sports/baseball_mlb/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,spreads,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return json_decode($contents, true);
                });


            $mlbbaseballDataArray = $mlbbaseballData;
            $filteredMlbbaseballData = array_filter($mlbbaseballDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $mlbbaseballLiveGameIds = array_column($filteredMlbbaseballData, 'id');


            foreach ($mlbbaseballLiveGameIds as $eventId) {

                $mlbbaseballLiveGameCacheTime = Cache::get('mlbbaseball_live_game_cache_time_' . $eventId);

                if (time() - $mlbbaseballLiveGameCacheTime >= 40) {

                    try {

                        $mlbbaseballResponse = $this->client->get("/v4/sports/baseball_mlb/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedMlbbaseballOdds = json_decode($mlbbaseballResponse->getBody()->getContents(), true);

                        Cache::put('mlbbaseball_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredMlbbaseballData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedMlbbaseballOdds;
                                break;
                            }
                        }
                        $mlbbaseballData = array_merge($mlbbaseballDataArray, $filteredMlbbaseballData);

                        $cacheKey = 'baseball_mlb_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('baseball_mlb_data', $mlbbaseballData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('mlbbaseball_live_game_cache_time_' . $eventId);
                        foreach ($mlbbaseballDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($mlbbaseballDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $mlbbaseballData = array_values($mlbbaseballDataArray);

                        $cacheKey = 'baseball_mlb_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $mlbbaseballData, $remainingTime);
                    }

                }
            }


            $responseArray = Utils::all([$mlbbaseballData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;

        } elseif ($sport == 'soccer') {


            $eplsoccerData =
                Cache::remember('soccer_epl_data', 120, function () {
                    $response = $this->client->get('/v4/sports/soccer_epl/odds', [
                        'query' => [
                            'apiKey' => env('API_KEY'),
                            'markets' => 'h2h,totals',
                            'regions' => 'us',
                            'oddsFormat' => 'decimal',
                            'bookmakers' => 'fanduel',
                        ]
                    ]);

                    $contents = $response->getBody()->getContents();
                    return json_decode($contents, true);
                });

            $eplsoccerDataArray = $eplsoccerData;
            $filteredEplsoccerData = array_filter($eplsoccerDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $eplsoccerLiveGameIds = array_column($filteredEplsoccerData, 'id');


            foreach ($eplsoccerLiveGameIds as $eventId) {

                $eplsoccerLiveGameCacheTime = Cache::get('eplsoccer_live_game_cache_time_' . $eventId);

                if (time() - $eplsoccerLiveGameCacheTime >= 40) {

                    try {

                        $eplsoccerResponse = $this->client->get("/v4/sports/soccer_epl/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedEplsoccerOdds = json_decode($eplsoccerResponse->getBody()->getContents(), true);

                        Cache::put('eplsoccer_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredEplsoccerData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedEplsoccerOdds;
                                break;
                            }
                        }
                        $eplsoccerData = array_merge($eplsoccerDataArray, $filteredEplsoccerData);

                        $cacheKey = 'soccer_epl_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('soccer_epl_data', $eplsoccerData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('eplsoccer_live_game_cache_time_' . $eventId);
                        foreach ($eplsoccerDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($eplsoccerDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $eplsoccerData = array_values($eplsoccerDataArray);

                        $cacheKey = 'soccer_epl_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $eplsoccerData, $remainingTime);
                    }
                }
            }

            $eflcupData = Cache::remember('soccer_england_efl_cup_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_england_efl_cup/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });


            $eflcupDataArray = $eflcupData;
            $filteredEflcupData = array_filter($eflcupDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $eflcupLiveGameIds = array_column($filteredEflcupData, 'id');


            foreach ($eflcupLiveGameIds as $eventId) {

                $eflcupLiveGameCacheTime = Cache::get('eflcup_live_game_cache_time_' . $eventId);

                if (time() - $eflcupLiveGameCacheTime >= 40) {

                    try {

                        $eflcupResponse = $this->client->get("/v4/sports/soccer_england_efl_cup/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedEflcupOdds = json_decode($eflcupResponse->getBody()->getContents(), true);

                        Cache::put('eflcup_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredEflcupData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedEflcupOdds;
                                break;
                            }
                        }
                        $eflcupData = array_merge($eflcupDataArray, $filteredEflcupData);

                        $cacheKey = 'soccer_england_efl_cup_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('soccer_england_efl_cup_data', $eflcupData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('eflcup_live_game_cache_time_' . $eventId);
                        foreach ($eflcupDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($eflcupDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $eflcupData = array_values($eflcupDataArray);

                        $cacheKey = 'soccer_england_efl_cup_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $eflcupData, $remainingTime);
                    }
                }
            }

            $uefaData = Cache::remember('soccer_uefa_champs_league_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_uefa_champs_league/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $uefaDataArray = $uefaData;
            $filteredUefaData = array_filter($uefaDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $uefaLiveGameIds = array_column($filteredUefaData, 'id');


            foreach ($uefaLiveGameIds as $eventId) {

                $uefaLiveGameCacheTime = Cache::get('uefa_live_game_cache_time_' . $eventId);

                if (time() - $uefaLiveGameCacheTime >= 40) {

                    try {

                        $uefaResponse = $this->client->get("/v4/sports/soccer_uefa_champs_league/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedUefaOdds = json_decode($uefaResponse->getBody()->getContents(), true);

                        Cache::put('uefa_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredUefaData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedUefaOdds;
                                break;
                            }
                        }
                        $uefaData = array_merge($uefaDataArray, $filteredUefaData);

                        $cacheKey = 'soccer_uefa_champs_league_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('soccer_uefa_champs_league_data', $uefaData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('uefa_live_game_cache_time_' . $eventId);
                        foreach ($uefaDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($uefaDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $uefaData = array_values($uefaDataArray);

                        $cacheKey = 'soccer_uefa_champs_league_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $uefaData, $remainingTime);
                    }
                }
            }

            $champData = Cache::remember('soccer_efl_champ_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_efl_champ/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $champDataArray = $champData;
            $filteredChampData = array_filter($champDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $champLiveGameIds = array_column($filteredChampData, 'id');


            foreach ($champLiveGameIds as $eventId) {

                $champLiveGameCacheTime = Cache::get('champ_live_game_cache_time_' . $eventId);

                if (time() - $champLiveGameCacheTime >= 40) {


                    try {
                        $champResponse = $this->client->get("/v4/sports/soccer_efl_champ/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedChampOdds = json_decode($champResponse->getBody()->getContents(), true);

                        Cache::put('champ_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredChampData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedChampOdds;
                                break;
                            }
                        }
                        $champData = array_merge($champDataArray, $filteredChampData);

                        $cacheKey = 'soccer_efl_champ_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('soccer_efl_champ_data', $champData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('champ_live_game_cache_time_' . $eventId);
                        foreach ($champDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($champDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $champData = array_values($champDataArray);

                        $cacheKey = 'soccer_efl_champ_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $champData, $remainingTime);
                    }
                }

            }
            $bundesligaData = Cache::remember('soccer_germany_bundesliga_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_germany_bundesliga/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);
                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $bundesligaDataArray = $bundesligaData;
            $filteredBundesligaData = array_filter($bundesligaDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $bundesligaLiveGameIds = array_column($filteredBundesligaData, 'id');


            foreach ($bundesligaLiveGameIds as $eventId) {

                $bundesligaLiveGameCacheTime = Cache::get('bundesliga_live_game_cache_time_' . $eventId);

                if (time() - $bundesligaLiveGameCacheTime >= 40) {

                    try {

                        $bundesligaResponse = $this->client->get("/v4/sports/soccer_germany_bundesliga/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedBundesligaOdds = json_decode($bundesligaResponse->getBody()->getContents(), true);

                        Cache::put('bundesliga_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredBundesligaData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedBundesligaOdds;
                                break;
                            }
                        }
                        $bundesligaData = array_merge($bundesligaDataArray, $filteredBundesligaData);

                        $cacheKey = 'soccer_germany_bundesliga_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('soccer_germany_bundesliga_data', $bundesligaData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('bundesliga_live_game_cache_time_' . $eventId);
                        foreach ($bundesligaDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($bundesligaDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $bundesligaData = array_values($bundesligaDataArray);

                        $cacheKey = 'soccer_germany_bundesliga_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $bundesligaData, $remainingTime);
                    }
                }
            }

            $laligaData = Cache::remember('soccer_spain_la_liga_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_spain_la_liga/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });


            $laligaDataArray = $laligaData;
            $filteredLaligaData = array_filter($laligaDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $laligaLiveGameIds = array_column($filteredLaligaData, 'id');


            foreach ($laligaLiveGameIds as $eventId) {

                $laligaLiveGameCacheTime = Cache::get('laliga_live_game_cache_time_' . $eventId);

                if (time() - $laligaLiveGameCacheTime >= 40) {

                    try {

                        $laligaResponse = $this->client->get("/v4/sports/soccer_spain_la_liga/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedLaligaOdds = json_decode($laligaResponse->getBody()->getContents(), true);

                        Cache::put('laliga_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredLaligaData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedLaligaOdds;
                                break;
                            }
                        }
                        $laligaData = array_merge($laligaDataArray, $filteredLaligaData);
                        $cacheKey = 'soccer_spain_la_liga_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('soccer_spain_la_liga_data', $laligaData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('laliga_live_game_cache_time_' . $eventId);
                        foreach ($laligaDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($laligaDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $laligaData = array_values($laligaDataArray);

                        $cacheKey = 'soccer_spain_la_liga_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $laligaData, $remainingTime);
                    }
                }
            }

            $facupData = Cache::remember('soccer_fa_cup_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_fa_cup/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });



            $facupDataArray = $facupData;
            $filteredFacupData = array_filter($facupDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $facupLiveGameIds = array_column($filteredFacupData, 'id');


            foreach ($facupLiveGameIds as $eventId) {

                $facupLiveGameCacheTime = Cache::get('facup_live_game_cache_time_' . $eventId);

                if (time() - $facupLiveGameCacheTime >= 40) {

                    try {

                        $facupResponse = $this->client->get("/v4/sports/soccer_fa_cup/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedFacupOdds = json_decode($facupResponse->getBody()->getContents(), true);

                        Cache::put('facup_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredFacupData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedFacupOdds;
                                break;
                            }
                        }
                        $facupData = array_merge($facupDataArray, $filteredFacupData);

                        $cacheKey = 'soccer_fa_cup_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('soccer_fa_cup_data', $facupData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('facup_live_game_cache_time_' . $eventId);
                        foreach ($facupDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($facupDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $facupData = array_values($facupDataArray);

                        $cacheKey = 'soccer_fa_cup_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $facupData, $remainingTime);
                    }
                }
            }


            $campeonatoData = Cache::remember('soccer_brazil_campeonato_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_brazil_campeonato/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $campeonatoDataArray = $campeonatoData;
            $filteredCampeonatoData = array_filter($campeonatoDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $campeonatoLiveGameIds = array_column($filteredCampeonatoData, 'id');


            foreach ($campeonatoLiveGameIds as $eventId) {

                $campeonatoLiveGameCacheTime = Cache::get('campeonato_live_game_cache_time_' . $eventId);

                if (time() - $campeonatoLiveGameCacheTime >= 40) {

                    try {

                        $campeonatoResponse = $this->client->get("/v4/sports/soccer_brazil_campeonato/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedCampeonatoOdds = json_decode($campeonatoResponse->getBody()->getContents(), true);

                        Cache::put('campeonato_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredCampeonatoData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedCampeonatoOdds;
                                break;
                            }
                        }
                        $campeonatoData = array_merge($campeonatoDataArray, $filteredCampeonatoData);

                        $cacheKey = 'soccer_brazil_campeonato_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('soccer_brazil_campeonato_data', $campeonatoData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('campeonato_live_game_cache_time_' . $eventId);
                        foreach ($campeonatoDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($campeonatoDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $campeonatoData = array_values($campeonatoDataArray);

                        $cacheKey = 'soccer_brazil_campeonato_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $campeonatoData, $remainingTime);
                    }
                }
            }

            $turkeysuperData = Cache::remember('soccer_turkey_super_league_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_turkey_super_league/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $turkeysuperDataArray = $turkeysuperData;
            $filteredTurkeysuperData = array_filter($turkeysuperDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $turkeysuperLiveGameIds = array_column($filteredTurkeysuperData, 'id');


            foreach ($turkeysuperLiveGameIds as $eventId) {

                $turkeysuperLiveGameCacheTime = Cache::get('turkeysuper_live_game_cache_time_' . $eventId);

                if (time() - $turkeysuperLiveGameCacheTime >= 40) {


                    try {
                        $turkeysuperResponse = $this->client->get("/v4/sports/soccer_turkey_super_league/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedTurkeysuperOdds = json_decode($turkeysuperResponse->getBody()->getContents(), true);

                        Cache::put('turkeysuper_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredTurkeysuperData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedTurkeysuperOdds;
                                break;
                            }
                        }
                        $turkeysuperData = array_merge($turkeysuperDataArray, $filteredTurkeysuperData);

                        $cacheKey = 'soccer_turkey_super_league_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('soccer_turkey_super_league_data', $turkeysuperData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('turkeysuper_live_game_cache_time_' . $eventId);
                        foreach ($turkeysuperDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($turkeysuperDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $turkeysuperData = array_values($turkeysuperDataArray);

                        $cacheKey = 'soccer_turkey_super_league_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $turkeysuperData, $remainingTime);
                    }
                }
            }

            $englandData = Cache::remember('soccer_england_league1_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_england_league1/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $englandDataArray = $englandData;
            $filteredEnglandData = array_filter($englandDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $englandLiveGameIds = array_column($filteredEnglandData, 'id');


            foreach ($englandLiveGameIds as $eventId) {

                $englandLiveGameCacheTime = Cache::get('england_live_game_cache_time_' . $eventId);

                if (time() - $englandLiveGameCacheTime >= 40) {

                    try {

                        $englandResponse = $this->client->get("/v4/sports/soccer_england_league1/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedEnglandOdds = json_decode($englandResponse->getBody()->getContents(), true);

                        Cache::put('england_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredEnglandData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedEnglandOdds;
                                break;
                            }
                        }
                        $englandData = array_merge($englandDataArray, $filteredEnglandData);

                        $cacheKey = 'soccer_england_league1_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put('soccer_england_league1_data', $englandData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('england_live_game_cache_time_' . $eventId);
                        foreach ($englandDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($englandDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $englandData = array_values($englandDataArray);

                        $cacheKey = 'soccer_england_league1_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $englandData, $remainingTime);
                    }
                }
            }

            $australiaData = Cache::remember('soccer_australia_aleague_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_australia_aleague/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);
                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $australiaDataArray = $australiaData;
            $filteredAustraliaData = array_filter($australiaDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $australiaLiveGameIds = array_column($filteredAustraliaData, 'id');


            foreach ($australiaLiveGameIds as $eventId) {

                $australiaLiveGameCacheTime = Cache::get('australia_live_game_cache_time_' . $eventId);

                if (time() - $australiaLiveGameCacheTime >= 40) {


                    try {
                        $australiaResponse = $this->client->get("/v4/sports/soccer_australia_aleague/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedAustraliaOdds = json_decode($australiaResponse->getBody()->getContents(), true);

                        Cache::put('australia_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredAustraliaData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedAustraliaOdds;
                                break;
                            }
                        }
                        $australiaData = array_merge($australiaDataArray, $filteredAustraliaData);
                        $cacheKey = 'soccer_australia_aleague_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('soccer_australia_aleague_data', $australiaData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('australia_live_game_cache_time_' . $eventId);
                        foreach ($australiaDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($australiaDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $australiaData = array_values($australiaDataArray);

                        $cacheKey = 'soccer_australia_aleague_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $australiaData, $remainingTime);
                    }
                }
            }

            $chinasuperData = Cache::remember('soccer_china_superleague_data', 120, function () {
                $response = $this->client->get('/v4/sports/soccer_china_superleague/odds', [
                    'query' => [
                        'apiKey' => env('API_KEY'),
                        'markets' => 'h2h,spreads,totals',
                        'regions' => 'us',
                        'oddsFormat' => 'decimal',
                        'bookmakers' => 'fanduel',
                    ]
                ]);

                $contents = $response->getBody()->getContents();

                return json_decode($contents, true);
            });

            $chinasuperDataArray = $chinasuperData;
            $filteredChinasuperData = array_filter($chinasuperDataArray, function ($game) {
                $current_time = Carbon::now();
                return Carbon::parse($game['commence_time']) <= $current_time;
            });

            $chinasuperLiveGameIds = array_column($filteredChinasuperData, 'id');


            foreach ($chinasuperLiveGameIds as $eventId) {

                $chinasuperLiveGameCacheTime = Cache::get('chinasuper_live_game_cache_time_' . $eventId);

                if (time() - $chinasuperLiveGameCacheTime >= 40) {


                    try {
                        $chinasuperResponse = $this->client->get("/v4/sports/soccer_china_superleague/events/{$eventId}/odds", [
                            'query' => [
                                'apiKey' => env('API_KEY'),
                                'markets' => 'h2h,totals',
                                'regions' => 'us',
                                'oddsFormat' => 'decimal',
                                'bookmakers' => 'fanduel',
                            ]
                        ]);

                        $updatedChinasuperOdds = json_decode($chinasuperResponse->getBody()->getContents(), true);

                        Cache::put('chinasuper_live_game_cache_time_' . $eventId, time(), 40);


                        foreach ($filteredChinasuperData as $game) {
                            if ($game['id'] == $eventId) {
                                $game = $updatedChinasuperOdds;
                                break;
                            }
                        }
                        $chinasuperData = array_merge($chinasuperDataArray, $filteredChinasuperData);

                        $cacheKey = 'soccer_china_superleague_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);

                        Cache::put('soccer_china_superleague_data', $chinasuperData, $remainingTime);

                    } catch (\Exception $e) {
                        // Delete cache and corresponding data from the main array
                        Cache::forget('chinasuper_live_game_cache_time_' . $eventId);
                        foreach ($chinasuperDataArray as $key => $game) {
                            if ($game['id'] == $eventId) {
                                unset($chinasuperDataArray[$key]);
                                break;
                            }
                        }
                        // Merge the updated array after removing the game data
                        $chinasuperData = array_values($chinasuperDataArray);

                        $cacheKey = 'soccer_china_superleague_data';

                        $expirationTime = Cache::get($cacheKey . ':expires_at');

                        $currentTime = Carbon::now();
                        $remainingTime = $currentTime->diffInSeconds($expirationTime);
                        Cache::put($cacheKey, $chinasuperData, $remainingTime);
                    }
                }
            }

            $responseArray = Utils::all([$eplsoccerData, $eflcupData, $uefaData, $champData, $bundesligaData, $laligaData, $facupData, $campeonatoData, $turkeysuperData, $englandData, $australiaData, $chinasuperData])->wait();

            $result = [];

            foreach ($responseArray as $file) {

                $decodedFile = $file;
                $result = array_merge($result, $decodedFile);
            }



            return $result;

        } else {
            // echo "nothing is happening";
        }
    }
    public function getScores()
    {
        $scores = Cache::remember('recent_scores', 120, function () {
            $response = $this->client->get('/v4/sports/scores');

            return json_decode($response->getBody(), true);
        });


        // return view('scores', compact('scores'));
        return response()->json($scores);

    }
}