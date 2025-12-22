<?php

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\App\Meilisearch\MeiliTrait;
use AcMarche\Courrier\Search\MeiliIndexer;
use Illuminate\Console\Command;

class MeiliIndexerCommand extends Command
{
    use MeiliTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courrier:meili-indexer {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $date = $this->argument('date');
        $indexName = config('courrier.meilisearch.index_name');
        $meiliServer = new MeiliIndexer($indexName);
        foreach (range(2010, date('Y')) as $year) {
            $meiliServer->addCourriersByYear($year);
        }
        if ($year && $year > 2010) {
            $meiliServer->addCourriersByYear($year);
        } elseif ($date) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
            if ($dateTime instanceof \DateTime) {
                $meiliServer->addCourriersByDate($dateTime);
            } else {
                $this->error('Invalid date format : '.$date);
            }
        }

    }


}
