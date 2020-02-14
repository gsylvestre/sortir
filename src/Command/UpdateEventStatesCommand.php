<?php

namespace App\Command;

use App\Entity\Event;
use App\EventState\EventStateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateEventStatesCommand extends Command
{
    protected static $defaultName = 'app:update-event-states';

    /** @var EntityManagerInterface */
    private $em;

    private $logger;

    /** @var EventStateHelper */
    private $stateHelper;

    public function __construct(EntityManagerInterface $em, EventStateHelper $stateHelper, LoggerInterface $logger, string $name = null)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->stateHelper = $stateHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('event states update started');

        $io = new SymfonyStyle($input, $output);

        $eventRepo = $this->em->getRepository(Event::class);
        $events = $eventRepo->findBy([]);

        foreach($events as $event) {
            if ($this->stateHelper->shouldChangeStateToClosed($event)){
                $this->stateHelper->changeEventState($event, "closed");
                $message = $event->getId() . " " . $event->getName() . " : statut changé en closed";
                $io->writeln($message);
                $this->logger->info($message);
            }

            if ($this->stateHelper->shouldChangeStateToOngoing($event)){
                $this->stateHelper->changeEventState($event, "ongoing");
                $message = $event->getId() . " " . $event->getName() . " : statut changé en ongoing";
                $io->writeln($message);
                $this->logger->info($message);
            }

            if ($this->stateHelper->shouldChangeStateToEnded($event)){
                $this->stateHelper->changeEventState($event, "ended");
                $message = $event->getId() . " " . $event->getName() . " : statut changé en ended";
                $io->writeln($message);
                $this->logger->info($message);
            }

            if ($this->stateHelper->shouldChangeStateToArchived($event)){
                $this->stateHelper->changeEventState($event, "archived");
                $message = $event->getId() . " " . $event->getName() . " : statut changé en archived";
                $io->writeln($message);
                $this->logger->info($message);
            }
        }

        $io->success("OK c'est fait !");
        $this->logger->info('event states update ended');

        return 0;
    }
}
