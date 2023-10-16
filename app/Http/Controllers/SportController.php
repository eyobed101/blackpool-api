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
                'Authorization' => 'API_KEY', 
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getGames()
    {
        $response = $this->client->get('/odds'); 

        $Games = json_decode($response->getBody(), true);
        
        return view('games', compact('Games'));
    }

    public function getScores()
    {
        $response = $this->client->get('/scores'); 

        $Scores = json_decode($response->getBody(), true);
        
        return view('scores', compact('Scores'));
    }
}
