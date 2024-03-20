<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ScoreController;

class FetchScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:scores';
    protected $description = 'Fetch scores from API and store in the database';

    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $scoreService = new ScoreController();
        $scoreService->getScoresScheduled();
    }

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     *
     * @return int
     */
}
