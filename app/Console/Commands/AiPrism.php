<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\ValueObjects\Messages\Support\Document;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

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

    protected array $meals = ['PAIN BLANC', 'PAIN GRIS', 'BLANC SS CROUTE', 'GRIS SS CROUTES', 'BEURRE/BECEL'];
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
        $schemaMeals = new ObjectSchema('meal', 'A meal', [
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
                new ArraySchema('meals', 'The meals name as a array of object', $schemaMeals),
                new StringSchema('comment', 'The comment as a string or null'),
            ],
            requiredFields: ['client', 'roomNumber', 'meals', 'comment']
        );

        try {
            $response =
                Prism::structured()
                    ->using(Provider::Anthropic, 'claude-4-sonnet-20250514')
                    ->withSchema($schema)
                    ->withMessages([
                        new UserMessage(
                            "with this attachment,
                            There is a table
                            The first row is the name of the days: ".join(',', $this->days).".
                            On the left, in the first column, you have the client names and their room numbers
                            They are 3 rows by client
                            One the first line, is structured as follows:
                            A first column with their name and room number
                            The cell is structured like this: name - room number
                            A second column with the name of the dish
                            And for the other 7 columns, the choice made for each day

                            On the seconde row, first column is empty
                            The second column is the name of the dish
                            And for the other 7 columns, the choice made for each day

                            One the third row, first column is empty
                            The second column is named remarque
                            The cell is merged on the 7 columns of day names

                            Extract the data from this table and give me the response in JSON format,
                            following the schema I gave you.
                            If you can't get the guest's name and room number, don't include them in the response.",
                            [
                                Document::fromPath('/home/jfsenechal/Desktop/mrs/new-v3-20250522101036324.pdf'),
                            ]
                        ),
                    ])
                    ->asStructured();

            try {
                Storage::disk('project_output')->put(
                    'resultFull.json',
                    json_encode($response, flags: JSON_THROW_ON_ERROR)
                );

            } catch (\Exception $e) {
                $this->error('full error '.$e->getMessage());
                dump($response);
            }

            Storage::disk('project_output')->put(
                'resultStructured.json',
                json_encode($response->text, flags: JSON_THROW_ON_ERROR)
            );

            Cache::set(self::ORDERS, json_encode($response->structured));

        } catch (PrismException|\Exception $e) {
            $this->error('Text generation failed:'.$e->getMessage());
        } catch (Throwable $e) {
            $this->error(('Generic error:'.$e->getMessage()));
        }
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
