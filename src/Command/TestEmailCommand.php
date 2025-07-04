<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestEmailCommand extends Command
{

    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:test-email')
            ->setDescription('Test d\'envoi d\'un email via Symfony Mailer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
    ->from('chams2002bejaoui@gmail.com')
    ->to('chams2002bejaoui@gmail.com')
    ->subject('Test mail à moi-même')
    ->text('Ceci est un test envoyé à ma propre adresse.');

        try {
            $this->mailer->send($email);
            $output->writeln('✅ Mail envoyé avec succès.');
        } catch (\Exception $e) {
            $output->writeln('❌ Erreur lors de l\'envoi du mail : ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
