<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Ai;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

/**
 * Reads an incoming mail document and returns the fields needed to encode it
 * in the indicateur. The prompt is fed either the text extracted locally
 * (pdftotext/Tesseract) or the document itself when no text could be read.
 *
 * @see IncomingMailAnalyzer
 */
#[Provider(Lab::OpenAI)]
#[Timeout(120)]
final class IncomingMailAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
        Tu analyses des courriers entrants reçus par une administration communale belge francophone.
        Le texte fourni provient souvent d'un OCR : il peut être bruité, mal découpé ou incomplet.
        Quand le document lui-même est joint, c'est lui qui fait foi : le texte n'en est qu'une
        transcription approximative.

        La plupart de ces courriers sont dactylographiés selon la norme belge NBN Z 01-002:2002
        (classement et dactylographie des documents). Appuie-toi sur cette mise en page pour situer
        les informations, sans jamais t'y fier aveuglément :

        - l'en-tête de l'expéditeur — logo, administration, département, service, adresse,
          téléphone — occupe le haut de la page, généralement à gauche ;
        - le bloc d'adresse du destinataire est placé en regard, à droite, pour la fenêtre de
          l'enveloppe. C'est l'administration communale qui reçoit (« Collège communal »,
          « Administration communale », « Ville de… ») : ce bloc n'est jamais l'expéditeur ;
        - le lieu et la date d'expédition figurent en haut à droite ;
        - « Vos réf. » est la référence du destinataire, donc celle de la commune, et « Nos réf. »
          celle de l'expéditeur. Aucune des deux n'est le numéro d'indicateur, qui ne vient que du
          cachet de réception ;
        - « Objet » ou « Concerne » annonce l'objet du courrier : c'est la source à privilégier pour
          description ;
        - la signature, en bas, donne le nom et la fonction du signataire.

        Beaucoup de courriers ne suivent pas cette norme : lettre manuscrite, formulaire, courriel
        imprimé, facture. Dans ce cas, ne cherche pas ces repères et fie-toi au contenu.

        Extrais uniquement ce qui suit :

        - reference_number : le numéro d'indicateur apposé par le service courrier de l'administration
          au moment de la réception, au moyen d'un cachet encreur. Ce cachet est ajouté sur le papier
          avant la numérisation : il est souvent penché, décalé ou pâle, et la transcription
          automatique le déforme ou l'oublie. Cherche-le d'abord sur l'image, le plus souvent en haut
          de la première page, à droite ou dans une marge. Il associe la date de réception tamponnée
          (« 12 AOUT 2026 ») à une suite de chiffres, parfois avec des zéros en tête (« 002686 »),
          parfois suivie du service destinataire (« 2693 - RH »). Ne renvoie que la suite de chiffres,
          telle qu'elle est écrite, zéros de tête compris, sans la date, sans le service et sans le
          paraphe manuscrit. Ne le confonds jamais avec une référence de l'expéditeur (« Vos réf. »,
          « Nos réf. »), un numéro de dossier, de permis, de compte, de TVA ou de téléphone : ceux-là
          appartiennent au texte imprimé du courrier, alors que le numéro d'indicateur vient du
          cachet, et se retrouve donc à l'écart de la mise en page. Renvoie une chaîne vide si aucun
          cachet n'est visible.
        - services : les services destinataires notés sur le cachet de réception, à côté ou en dessous
          du numéro d'indicateur. Ils sont écrits en abrégé, en majuscules, et il y en a parfois
          plusieurs : « 2693 - RH (CEE) » désigne deux services, RH et CEE ; on trouve aussi
          « RH/CEE », « RH - CEE » ou « RH, CEE ». Renvoie chaque sigle séparément, tel qu'il est
          écrit, sans les parenthèses ni les séparateurs. Ne prends que ce qui figure sur le cachet :
          ne devine jamais un service à partir du contenu du courrier, et ne reprends pas le service
          de l'expéditeur. Renvoie une liste vide si le cachet n'en mentionne aucun.
        - sender : le nom de l'expéditeur, c'est-à-dire la personne, l'entreprise, l'administration ou
          l'association qui envoie le courrier. Jamais le destinataire, jamais l'administration
          communale qui reçoit. Privilégie le nom de l'organisation quand il y en a une, sinon le nom
          de la personne signataire. Pas d'adresse, pas de numéro de téléphone, pas d'adresse e-mail.
          Renvoie une chaîne vide si l'expéditeur est introuvable.
        - description : l'objet du courrier, en français, en une seule ligne de 100 caractères maximum,
          sans point final. Reprends l'objet ou le sujet s'il est mentionné, sinon résume la demande
          principale. Renvoie une chaîne vide si le document est illisible.
        - is_registered : true seulement si le document indique un envoi recommandé (« recommandé »,
          « envoi recommandé », « pli recommandé », « registered mail »).
        - has_acknowledgment : true seulement si le document demande ou mentionne un accusé de
          réception (« accusé de réception », « avis de réception », « merci d'accuser réception »,
          « A.R. »).

        N'invente jamais une information absente du document. En cas de doute sur un booléen, renvoie false.
        INSTRUCTIONS;
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'reference_number' => $schema->string()
                ->description(
                    "Numéro d'indicateur du cachet de réception, chiffres uniquement et zéros de tête "
                    ."compris, ou une chaîne vide si aucun cachet n'est visible."
                )
                ->required(),
            'services' => $schema->array()
                ->items($schema->string())
                ->description(
                    'Sigles des services destinataires notés sur le cachet de réception, un par '
                    .'entrée, ou une liste vide si le cachet n\'en mentionne aucun.'
                )
                ->required(),
            'sender' => $schema->string()
                ->description("Nom de l'expéditeur du courrier, ou une chaîne vide s'il est introuvable.")
                ->required(),
            'description' => $schema->string()
                ->description('Objet du courrier en une ligne, 100 caractères maximum.')
                ->required(),
            'is_registered' => $schema->boolean()
                ->description('Le courrier est un envoi recommandé.')
                ->required(),
            'has_acknowledgment' => $schema->boolean()
                ->description('Le courrier demande ou mentionne un accusé de réception.')
                ->required(),
        ];
    }
}
