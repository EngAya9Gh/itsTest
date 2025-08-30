<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\AppSection;
use App\Models\DataCommunicationSection;
use App\Models\Tweetcell;
use App\Models\TweetcellSection;

use Illuminate\Support\Facades\Http;

class FetchGamesFromTweetCell extends Command
{
  protected $signature = 'fetch:games';
    protected $description = 'Fetch games from the API and update prices in the database';

    public function handle()
    {
       $tweetcells = Tweetcell::all();
  
       
       
    foreach ($tweetcells as $s) {
      $t=TweetcellSection::findOrFail($s->section_id);
        $s->price =$s->basic_price+$s->basic_price*$t->increase_percentage/100;
          $s->save();
        
    }
   
    return "tweetcells updated successfully!";
      

    }
}
