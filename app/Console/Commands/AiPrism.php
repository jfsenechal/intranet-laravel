<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
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

    /**
     * Execute the console command.
     */
    public function handle()
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
        foreach ($data->orders as $order) {
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
        $this->table(['Client', 'Room', 'Meals 1', 'Meals 2'], $rows);
    }

    private function execIa(): void
    {
        $schema = new ObjectSchema(
            name: 'meal_schema',
            description: 'A structured meal',
            properties: [
                new StringSchema('client', 'The last name of the client as a string'),
                new StringSchema('roomNumber', 'an integer'),
                new StringSchema('meals', 'The meals name as a array, key is the meal name, value is the quantity'),
            ],
            requiredFields: ['client', 'roomNumber', 'meals']
        );

        try {
            $response =
                Prism::structured()
                    ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
                    ->withSchema($schema)
                    ->withMessages([
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
                json_encode($response->structured, flags: JSON_THROW_ON_ERROR)
            );

            Cache::set(self::ORDERS, json_encode($response->structured));

        } catch (PrismException|\Exception $e) {
            $this->error('Text generation failed:'.$e->getMessage());
        } catch (Throwable $e) {
            $this->error(('Generic error:'.$e->getMessage()));
        }
    }
}
