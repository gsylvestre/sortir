<?php

namespace App\Command;

use App\Entity\Event;
use App\EventState\EventStateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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

    /** @var EventStateHelper */
    private $stateHelper;

    public function __construct(EntityManagerInterface $em, EventStateHelper $stateHelper, string $name = null)
    {
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
        $io = new SymfonyStyle($input, $output);

        $eventRepo = $this->em->getRepository(Event::class);
        $events = $eventRepo->findBy([]);

        foreach($events as $event) {
            if ($this->stateHelper->shouldChangeStateToClosed($event)){
                $this->stateHelper->changeEventState($event, "closed");
                $io->writeln($event->getId() . " " . $event->getName() . " : statut changé en closed");
            }

            if ($this->stateHelper->shouldChangeStateToOngoing($event)){
                $this->stateHelper->changeEventState($event, "ongoing");
                $io->writeln($event->getId() . " " . $event->getName() . " : statut changé en ongoing");
            }

            if ($this->stateHelper->shouldChangeStateToEnded($event)){
                $this->stateHelper->changeEventState($event, "ended");
                $io->writeln($event->getId() . " " . $event->getName() . " : statut changé en ended");
            }

            if ($this->stateHelper->shouldChangeStateToArchived($event)){
                $this->stateHelper->changeEventState($event, "archived");
                $io->writeln($event->getId() . " " . $event->getName() . " : statut changé en archived");
            }
        }

        $io->success("OK c'est fait !");

        return 0;
    }
}
