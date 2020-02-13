<?php


namespace App\EventState;


use App\Entity\Event;
use App\Entity\EventState;
use Doctrine\Persistence\ManagerRegistry;

class EventStateHelper
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getStateByName(string $name)
    {
        $stateRepo = $this->doctrine->getRepository(EventState::class);
        $state = $stateRepo->findOneBy(['name' => $name]);

        return $state;
    }

    public function changeEventState(Event $event, string $newStateName)
    {
        $newState = $this->getStateByName($newStateName);
        $event->setState($newState);

        $em = $this->doctrine->getManager();
        $em->persist($event);
        $em->flush();
    }
}