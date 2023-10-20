<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;


use Illuminate\Http\Request;

class SportController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.the-odds-api.com',
            'headers' => [
                'Authorization' => '8b0b6949dd4456a8534cd76543bc3c7e',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getGames()
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
            return $this->client->get('/v4/sports/baseball_mlb/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getNCAABasketball  = function () {
            return $this->client->get('/v4/sports/basketball_ncaab/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getNBA = function () {
            return $this->client->get('/v4/sports/basketball_nba/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'draftkings',
            ]);
        };

       

        $getNCAAFootball = function () {
            return $this->client->get('/v4/sports/americanfootball_ncaaf/odds', [
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
        
        $getCFL= function () {
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

        $getATPAOpen    = function () {
            return $this->client->get('/v4/sports/tennis_atp_aus_open_singles/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getWTAFOpen    = function () {
            return $this->client->get('/v4/sports/tennis_atp_french_open/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        $getEPL    = function () {
            return $this->client->get('/v4/sports/soccer_epl/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        $getEFLC	    = function () {
            return $this->client->get('/v4/sports/soccer_england_efl_cup/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        $getUEFACLeague	= function () {
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
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };

        $getGBundesliga = function () {
            return $this->client->get('/v4/sports/soccer_germany_bundesliga/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        $getLaSpain	    = function () {
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
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        $getBrazilSA = function () {
            return $this->client->get('/v4/sports/soccer_brazil_campeonato/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        $getTurkeyLeague = function () {
            return $this->client->get('/v4/sports/soccer_turkey_super_league/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        
        $getLigue1 = function () {
            return $this->client->get('/v4/sports/soccer_england_league1/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        $getALeague = function () {
            return $this->client->get('/v4/sports/soccer_australia_aleague/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        $getSuperChina = function () {
            return $this->client->get('/v4/sports/soccer_china_superleague/odds', [
                'markets' => 'h2h,spreads,totals',
                'regions' => 'us',
                'oddsFormat' => 'american',
                'bookmakers' => 'fanduel',
            ]);
        };
        
        

       
        $games = Cache::remember('recent_odds', 3600, function () {
            $response = $this->client->get('/v4/sports/upcoming/odds', [
                'query' => [
                    'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                    'regions' => "uk",
                    'markets' => "h2h,totals",
                    'oddsFormat' => "decimal"
                ]
            ]);
            echo "Remaining requests: " . $response->getHeader('x-requests-remaining')[0];
            return json_decode($response->getBody(), true);
        });

        // return view('games', compact('games'));
        return response()->json($games);

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