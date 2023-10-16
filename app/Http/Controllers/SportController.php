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
        $games = Cache::remember('recent_odds', 3600, function () {
            $response = $this->client->get('/V4/sport/upcoming/odds'); 
            return json_decode($response->getBody(), true);
        });

        // return view('games', compact('games'));
        return response()->json($games);

    }

    public function getScores()
    {
        $scores = Cache::remember('recent_scores', 60, function () {
            $response = $this->client->get('/V4/sport/scores'); 
            return json_decode($response->getBody(), true);
        });

        // return view('scores', compact('scores'));
        return response()->json($scores);

    }
}