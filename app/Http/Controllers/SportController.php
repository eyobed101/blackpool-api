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

    public function getGames(Request $request)
    {
        $sport = $request->input('sport');
        $region = $request->input('region');
        $market = $request->input('market');


        $params = [
            'query' => [
               'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
            ]
         ];
        $games = Cache::remember('recent_odds', 3600, function () {
            $response = $this->client->get('/v4/sports/' . $sport . '/odds', [
                'query' => [
                   'apiKey' => "8b0b6949dd4456a8534cd76543bc3c7e",
                   'sport' => $sport,
                   'region' => $region,
                   'market' => $market,
                   'oddsFormat' => "decimal"
                ]
             ]); 
            $output = new \Symfony\Component\Console\Output\ConsoleOutput(2);
            $output->writeln($response->getHeader());

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