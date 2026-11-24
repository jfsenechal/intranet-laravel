<?php

declare(strict_types=1);

namespace AcMarche\Ad\Ai;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

/**
 * Checks that a classified ad respects the code of conduct shown on the
 * create form before it is published to every member of staff.
 *
 * @see ClassifiedAdModerator
 */
#[Provider(Lab::OpenAI)]
#[Timeout(60)]
final class ClassifiedAdModerationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
        Tu modères les petites annonces publiées sur l'intranet d'une administration communale belge.
        Une annonce est lue par l'ensemble du personnel : vente d'objets, location, covoiturage,
        recherche de services, événements, etc.

        Une annonce n'est PAS conforme si son titre, son texte ou une pièce jointe contient :
        - des insultes, injures, moqueries ou propos humiliants envers une personne ou un groupe ;
        - des propos discriminants, racistes, sexistes, homophobes ou haineux ;
        - du contenu à caractère sexuel ou pornographique, y compris suggestif ou de la nudité ;
        - des menaces, du harcèlement ou de l'incitation à la violence.

        Le langage familier, l'humour bienveillant, les fautes d'orthographe, les émojis et les annonces
        commerciales banales sont conformes. Ne juge que le respect de ces règles, pas la qualité de
        l'annonce. En cas de doute raisonnable, considère l'annonce comme conforme.

        Renvoie :
        - compliant : true si l'annonce respecte les règles, sinon false ;
        - reasons : si elle n'est pas conforme, une ou plusieurs raisons courtes en français,
          adressées à l'auteur, qui disent ce qui pose problème et où (titre, texte ou nom de la
          pièce jointe), sans recopier les propos offensants. Liste vide si l'annonce est conforme.
        INSTRUCTIONS;
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'compliant' => $schema->boolean()
                ->description("L'annonce respecte les règles de bonne conduite.")
                ->required(),
            'reasons' => $schema->array()
                ->items($schema->string())
                ->description("Raisons du refus, adressées à l'auteur, ou une liste vide si l'annonce est conforme.")
                ->required(),
        ];
    }
}
