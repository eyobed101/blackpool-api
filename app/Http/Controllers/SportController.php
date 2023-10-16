<?php


namespace App\Http\Controllers;

use GuzzleHttp\Client;


use Illuminate\Http\Request;
class SportController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.the-odds-api.com/V4/sport', 
            'headers' => [
                'Authorization' => '8b0b6949dd4456a8534cd76543bc3c7e', 
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getGames()
    {
        $games = Cache::remember('recent_odds', 60, function () {
            $response = $this->client->get('/odds'); 
            return json_decode($response->getBody(), true);
        });

        return view('games', compact('games'));
    }

    public function getScores()
    {
        $scores = Cache::remember('recent_scores', 60, function () {
            $response = $this->client->get('/scores'); 
            return json_decode($response->getBody(), true);
        });

        return view('scores', compact('scores'));
    }
}