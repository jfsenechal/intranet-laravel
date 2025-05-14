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
                            "Avec le document joint,
                            extrait le tableau dans un format json
                            dans la colonne de droite c'est le nom des clients suivit d'un tirer puis du numéro de chambre
                            met ses 2 valeurs dans 2 champs séparés
                            dans la première ligne du haut du tableau ce sont le nom des plats
                            si tu n'arrives pas à avoir le nom du client et sa chambre, ne le met pas dans la réponse json
                            ne me donne que le json, je n'ai pas besoin d'explication",
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
                //dump($response);
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
