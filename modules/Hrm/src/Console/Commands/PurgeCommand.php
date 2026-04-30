<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use Illuminate\Console\Command;
use Override;

final class PurgeCommand extends Command
{
    #[Override]
    protected $signature = 'hrm:purge';

    #[Override]
    protected $description = 'Purge: delete old records from tables';

    public function handle(): void
    {
        $args = [
            'statut' => Statuts::CANDIDATURE,
        ];
        $candidats = $this->employeRepository->findBy($args);
        $archives = [];

        $oneYearAgo = new DateTime();
        $oneYearAgo->modify('-1 year');

        foreach ($candidats as $employe) {
            $candidatures = $this->candidatureRepository->findRecent($employe, $oneYearAgo);
            if (count($candidatures) === 0) {
                $archives[] = $employe;
                $this->employeRepository->remove($employe);
            }
        }

        usort(
            $archives,
            function ($a, $b) {
                return $a->getNom() <=> $b->getNom();
            },
        );

        if ($archives !== []) {
            $message = (new TemplatedEmail())
                ->subject('Les candidats suivant ont été supprimés')
                ->from('no-reply@marche.be');
            foreach ($this->commandInit->getAll() as $email) {
                $message->addTo($email);
            }

            $message
                ->htmlTemplate('@AcMarcheGrh/archive/mail_auto_archive.html.twig')
                ->context(
                    [
                        'candidats' => $archives,
                    ],
                );

            try {
                $this->mailer->send($message);
            } catch (TransportExceptionInterface $e) {
                $io->error($e->getMessage().' archives');
            }

            $this->employeRepository->flush();
        }
    }
}
