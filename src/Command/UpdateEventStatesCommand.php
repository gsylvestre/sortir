<?php

namespace App\Command;

use App\Entity\Event;
use App\EventState\EventStateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande à exécuter à chaque minute pour l'éternité, avec un cron job à créer sur le serveur de prod
 * Cette commande permet de tenir à jour les états des sorties
 *
 * Class UpdateEventStatesCommand
 * @package App\Command
 */
class UpdateEventStatesCommand extends Command
{
    protected static $defaultName = 'app:update-event-states';

    /** @var EntityManagerInterface */
    private $em;

    private $logger;

    /** @var EventStateHelper */
    private $stateHelper;

    /**
     * On utilise l'injection de dépendance pour récupérer plein de classes utiles
     *
     * UpdateEventStatesCommand constructor.
     * @param EntityManagerInterface $em
     * @param EventStateHelper $stateHelper
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        EntityManagerInterface $em,
        EventStateHelper $stateHelper,
        LoggerInterface $logger,
        string $name = null
    )
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->stateHelper = $stateHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Met à jour les états des sorties')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('event states update started');

        $io = new SymfonyStyle($input, $output);

        //on charge carrément tous les sorties
        //@TODO: charger que seulement quelques sorties ? Genre pas besoin de charger les annulées ?
        $eventRepo = $this->em->getRepository(Event::class);
        $events = $eventRepo->findBy([]);

        //on les parcourt...
        foreach($events as $event) {
            //voir src/EventState/EventStateHelper.php pour ces fonctions...

            //doit-être changé en "closed" ?
            if ($this->stateHelper->shouldChangeStateToClosed($event)){
                //change en closed
                $this->stateHelper->changeEventState($event, "closed");
                //message pour la console et pour les logs
                $message = $event->getId() . " " . $event->getName() . " : statut changé en closed";
                //écrit le message dans la console
                $io->writeln($message);
                //puis dans les logs
                $this->logger->info($message);
                continue;
            }

            if ($this->stateHelper->shouldChangeStateToOngoing($event)){
                $this->stateHelper->changeEventState($event, "ongoing");
                $message = $event->getId() . " " . $event->getName() . " : statut changé en ongoing";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }

            if ($this->stateHelper->shouldChangeStateToEnded($event)){
                $this->stateHelper->changeEventState($event, "ended");
                $message = $event->getId() . " " . $event->getName() . " : statut changé en ended";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }

            if ($this->stateHelper->shouldChangeStateToArchived($event)){
                $this->stateHelper->changeEventState($event, "archived");
                $message = $event->getId() . " " . $event->getName() . " : statut changé en archived";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }
        }

        $io->success("OK c'est fait !");
        $this->logger->info('event states update ended');

        return 0;
    }
}
