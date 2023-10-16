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

        $params = [
            'query' => [
               'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
            ]
         ];
        $games = Cache::remember('recent_odds', 3600, function () {
            $response = $this->client->get('/v4/sports/upcoming/odds', [
                'query' => [
                   'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                   'regions' => "uk",
                   'markets' => "h2h",
                   'oddsFormat' => "decimal"
                ]
             ]); 

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