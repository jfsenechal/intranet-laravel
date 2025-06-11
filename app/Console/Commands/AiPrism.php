<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\ValueObjects\Messages\Support\Document;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Psr\SimpleCache\InvalidArgumentException;

class AiPrism extends Command
{
    const ORDERS = 'orders';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ai {--refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected array $meals = ['Choix 1/2', 'Sucré/Salé'];
    private array $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->option('refresh')) {
            Cache::delete(self::ORDERS);
        }

        if (!$result = Cache::get(self::ORDERS)) {
            $this->execIa();
            $result = Cache::get(self::ORDERS);
        }
        $rows = [];
        $data = json_decode($result);
        /*   foreach ($data->orders as $order) {
               $meals = [];
               foreach ($order->meals as $mealName => $quantity) {
                   $meals[] = $mealName.' '.$quantity;
               }
               $rows[] = [
                   $order->client,
                   $order->roomNumber,
                   $meals[0] ?? '',
                   $meals[1] ?? '',
               ];
           }
           $this->table(['Client', 'Room', 'Meals 1', 'Meals 2'], $rows);*/
    }

    private function execIa(): void
    {
        $mealSchemaObject = new ObjectSchema('mealObject', 'A meal', [
            new StringSchema('name', 'The name of the meal as a string'),
            new StringSchema('day', 'The name of the day as a string'),
            new StringSchema('value', 'The response of the meal as a string'),
        ]);

        $schema = new ObjectSchema(
            name: 'meal_schema',
            description: 'A structured meal',
            properties: [
                new StringSchema('client', 'The last name of the client as a string'),
                new NumberSchema('roomNumber', 'an integer'),
                new ArraySchema('meals', 'The meals as an array of object', $mealSchemaObject),
                new StringSchema('comment', 'The comment as a string or null'),
            ],
            requiredFields: ['client', 'roomNumber', 'meals', 'comment']
        );
        $model = "claude-3-5-sonnet-20241022";
        //$model = 'claude-4-sonnet-20250514';
        $response =
            Prism::text()
                ->using(Provider::Anthropic, $model)
                //->withSchema($schema)
                ->withMessages([
                    new UserMessage(
                        "extract from pdf file the table and return data json format
                        it's clients with meal order for each day",
                        [
                            Document::fromPath('/home/jfsenechal/Desktop/mrs/final-or-not-20250526161442422.pdf'),
                        ]
                    ),
                ])
                ->asText();

        dump($response);
        try {
            Storage::disk('project_output')->put(
                'resultStructured.json',
                $response->text
            );
            Cache::set(self::ORDERS, json_encode($response->text));
        } catch (InvalidArgumentException|\Exception $e) {
            dump($e->getMessage());
        }

        $this->info($response->finishReason->name);
    }

    private function promptSave(): void
    {
        new UserMessage(
            "with this attachment,
                            There is a table
                            First, turn the page 90 degrees to the right to have the table in the correct orientation
                            Above the table in the first row, there are the following menus: ".join(',', $this->meals).".
                            On the left, in the first column, you have the guest names and their room numbers
                            In the format NAME - ROOM NUMBER.
                            On each line of the customer, you have the quantity of menus chosen.
                            Extract the data from this table and give me the response in JSON format,
                            following the schema I gave you.
                            If you can't get the guest's name and room number, don't include them in the response.",
            [
                Document::fromPath('/var/www/scripts/ollama/ocr/20250407115630163.pdf'),
            ]
        );
    }
}
