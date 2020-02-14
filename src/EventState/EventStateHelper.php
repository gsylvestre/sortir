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

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être archivée
     *
     * @param Event $event
     * @return bool
     */
    public function shouldChangeStateToArchived(Event $event): bool
    {
        $oneMonthAgo = new \DateTime("-1 month");
        if (
            $event->getEndDate() < $oneMonthAgo &&
            $event->getState()->getName() !== "archived"
        ){
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "en cours"
     *
     * @param Event $event
     * @return bool
     */
    public function shouldChangeStateToOngoing(Event $event): bool
    {
        $now = new \DateTime();
        if (
            $event->getState()->getName() === "closed" &&
            $event->getStartDate() < $now &&
            $event->getEndDate() > $now &&
            $event->getState()->getName() !== "ongoing"
        ){
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "terminée"
     *
     * @param Event $event
     * @return bool
     */
    public function shouldChangeStateToEnded(Event $event): bool
    {
        $now = new \DateTime();
        if (
            $event->getState()->getName() === "ongoing" &&
            $event->getEndDate() < $now &&
            $event->getState()->getName() !== "ended"
        ){
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "fermée"
     *
     * @param Event $event
     * @return bool
     */
    public function shouldChangeStateToClosed(Event $event): bool
    {
        $now = new \DateTime();
        if (
            $event->getState()->getName() === "open" &&
            $event->getRegistrationLimitDate() <= $now &&
            $event->getState()->getName() !== "closed"
        ){
            return true;
        }

        return false;
    }
}