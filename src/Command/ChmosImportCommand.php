<?php

namespace App\Command;

use App\Entity\User;
use App\Service\ChmosService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:chmos:import')]
class ChmosImportCommand extends Command
{
    protected ManagerRegistry $doctrine;
    protected ChmosService $chmosService;
    protected MailerInterface $mailer;
    protected string $mailerFrom;
    protected string $host;

    public function __construct(ManagerRegistry $doctrine, ChmosService $chmosService, MailerInterface $mailer, string $mailerFrom, string $host)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->chmosService = $chmosService;
        $this->mailer = $mailer;
        $this->mailerFrom = $mailerFrom;
        $this->host = $host;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import chmos data.')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Import changes since (YYYY-MM-DD).')
            ->addOption('till', null, InputOption::VALUE_OPTIONAL, 'Import changes till (YYYY-MM-DD).')
            ->addOption('merge', null, InputOption::VALUE_NONE, 'Instantly merge new entries.')
            ->addOption('skip-notification', null, InputOption::VALUE_NONE, 'Skip notification.')
            ->addOption('project-codes', null, InputOption::VALUE_OPTIONAL, 'Comma separated list of project code to force sync.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $lastSyncFile = __DIR__.'/../../chmos-last-sync.flag';

        /** @var User[] $users */
        $users = $this->doctrine->getManager()->getRepository(User::class)->findAll();

        $notificationRecipients = [];

        foreach($users as $user) {
            if(!in_array('CHMOS_INBOX', $user->getNotifications())) {
                continue;
            }

            $notificationRecipients[] = $user->getEmail();
        }

        $since = null;
        $till = $input->getOption('till') ? new \DateTime($input->getOption('till')) : null;
        $merge = $input->getOption('merge');
        $skipNotification = $input->getOption('skip-notification');

        if(file_exists($lastSyncFile)) {
            if(trim(file_get_contents($lastSyncFile))) {
                $since = new \DateTime(file_get_contents($lastSyncFile));
                $since = $since->modify('+1 day');
            }
        }

        if(!$since) {
            $since = new \DateTime('2020-10-01');
        }

        if($input->getOption('since')) {
            $since = new \DateTime($input->getOption('since'));
        }

        $lastSync = new \DateTime();

        if($input->getOption('project-codes')) {

            $projectCodes = explode(',', $input->getOption('project-codes'));
            $projectCodes = array_map('trim', $projectCodes);

            foreach($projectCodes as $projectCode) {
                $this->chmosService->performProjectUpdate($projectCode, $merge);
            }

            $io->success(sprintf('CHMOS sync completed. %s items were updated.', count($projectCodes)));

            return 0;

        }

        $inboxItems = $this->chmosService->performUpdate($since, $till, $merge);

        if($till) {
            file_put_contents($lastSyncFile, $till->format('Y-m-d'));
        } else {
            file_put_contents($lastSyncFile, $lastSync->format('Y-m-d'));
        }

        if(!$skipNotification && count($inboxItems) && count($notificationRecipients)) {

            $email = (new Email())
                ->from($this->mailerFrom)
                ->to(...$notificationRecipients)
                ->subject(sprintf('📥 %s neue Elemente im Posteingang verfügbar', count($inboxItems)))
                ->text(
                    trim(sprintf('
                    
Hallo!

Es sind %s neue Elemente im Posteingang verfügbar.

Öffne den Posteingang (%s/#/inbox) um die Inhalte zu überprüfen und freizugeben.

Liebe Grüsse

                    ', count($inboxItems), $this->host)),
                )
            ;

            $this->mailer->send($email);

        }

        $io->success(sprintf('CHMOS sync completed. %s new items were found.', count($inboxItems)));

        return 0;
    }
}
