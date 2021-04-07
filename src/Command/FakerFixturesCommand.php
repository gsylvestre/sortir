<?php

namespace App\Command;

use App\Entity\EventCancelation;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\SchoolSite;
use App\Entity\User;
use App\Entity\EventState;
use App\Entity\City;
use App\Entity\Location;
use App\Entity\Event;
use App\Entity\EventSubscription;

/**
 * Cette commande permet de générer plein de données bidon.
 * La grosse base a été générée par l'extraordinaire package : gsylvestre/symfony-faker-fixtures :)
 * Attention : rien à voir avec les fixtures de Doctrine !!!
 *
 * Class FakerFixturesCommand
 * @package App\Command
 */
class FakerFixturesCommand extends Command
{
    protected static $defaultName = 'app:fixtures:load';

    /** @var SymfonyStyle */
    protected $io;
    /** @var \Faker\Generator **/
    protected $faker;
    /** @var ProgressIndicator **/
    protected $progress;
    /** @var \Doctrine\Bundle\DoctrineBundle\Registry **/
    protected $doctrine;
    /** @var UserPasswordEncoderInterface **/
    protected $passwordEncoder;

    public function __construct(ManagerRegistry $doctrine, UserPasswordEncoderInterface $passwordEncoder, $name = null)
    {
        parent::__construct($name);
        //faker nous permet de générer des données réalistes, en français
        $this->faker = \Faker\Factory::create("fr_FR");
        $this->doctrine = $doctrine;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this->setDescription('Load all fixtures');
    }

    /**
     * Cette méthode est exécuté quand on fait : php bin/console app:fixtures:load
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        //on continue que si c'est confirmé par le dév
        $confirmed = $this->io->confirm('This will delete all your database datas. Continue?', false);
        if (!$confirmed){
            $this->io->text("Ok then.");
            return 0;
        }

        //petite barre qui tourne dans la console
        $this->progress = new ProgressIndicator($output);
        $this->progress->start('Loading fixtures');

        //vide presque toutes les tables. Voir la méthode plus bas.
        $this->truncateTables();

        //l'ordre est important ici
        $this->loadSchoolSites();
        $this->loadCities();
        $this->loadUsers($num = 50);
        $this->loadLocations($num = 30);

        $this->loadEventStates();
        $this->loadEvents($num = 100);
        $this->loadEventSubscriptions($num = 500);
        $this->loadEventCancelations();

        //that's it
        $this->progress->finish("Done!");
        $this->io->success('Fixtures loaded!');
        return 0;
    }



    /**
     * Charge les états possibles d'événements
     */
    protected function loadEventStates():void
    {
        $stateNames = [
            "created" => "Créée",
            "open" => "Ouverte",
            "closed" => "Fermée",
            "ongoing" => "En cours",
            "ended" => "Terminée",
            "archived" => "Archivée",
            "canceled" => "Annulée"
        ];

        foreach($stateNames as $name => $prettyName){
            $state = new EventState();
            $state->setName($name);
            $state->setPrettyName($prettyName);
            $this->doctrine->getManager()->persist($state);
        }

        $this->doctrine->getManager()->flush();
    }

        /**
     * Charge les villes
     */
    protected function loadCities():void
    {
        $connection = $this->doctrine->getConnection();
        $stmt = $connection->prepare(file_get_contents(__DIR__ . "/city.sql"));
        $stmt->execute();
    }

    /**
     * Charge les écoles
     */
    protected function loadSchoolSites(): void
    {
        $this->progress->setMessage("loading schools");

        $names = ["La-Roche-Sur-Yon", "Nantes", "Rennes", "Niort"];

        foreach($names as $name){
            $school = new SchoolSite();
            $school->setName($name);
            $this->doctrine->getManager()->persist($school);
        }

        $this->doctrine->getManager()->flush();
    }

    /**
     * Charge des users bidons
     *
     * @param int $num
     * @throws \Exception
     */
    protected function loadUsers(int $num): void
    {
        $this->progress->setMessage("loading users");
        $allSchoolSites = $this->doctrine->getRepository(SchoolSite::class)->findAll();

        //2 utilisateurs écrits en dur pour s'en rappeler facilement :

        //utilisateur lambda, ROLE_USER
        $yo = new User();
        $hash = $this->passwordEncoder->encodePassword($yo, "yoyoyo");
        $yo->setPassword($hash);
        $yo->setLastname( "yo" );
        $yo->setFirstname( "yo" );
        $yo->setPhone( "0601020304" );
        $yo->setEmail("yo@yo.com");
        $yo->setIsAdmin( 0 );
        $yo->setIsActive( 1 );
        $yo->setCreatedDate( new \DateTime() );
        $yo->setSchool( $this->faker->randomElement($allSchoolSites) );

        $this->doctrine->getManager()->persist($yo);

        //admin
        $admin = new User();
        $hash = $this->passwordEncoder->encodePassword($admin, "admin");
        $admin->setPassword($hash);
        $admin->setLastname( "admin" );
        $admin->setFirstname( "admin" );
        $admin->setPhone( "0601020304" );
        $admin->setEmail("admin@admin.com");
        $admin->setIsAdmin( 1 );
        $admin->setIsActive( 1 );
        $admin->setCreatedDate( new \DateTime() );
        $admin->setSchool( $this->faker->randomElement($allSchoolSites) );

        $this->doctrine->getManager()->persist($admin);

        for($i=0; $i<$num; $i++){
            $user = new User();

            $user->setEmail( $this->faker->unique()->email );
            //no faker method found!
            //$user->setRoles( $this->faker-> );
            //password
            $plainPassword = "ryanryan";
            $hash = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($hash);
            $user->setLastname( $this->faker->lastName );
            $user->setFirstname( $this->faker->firstName );
            $user->setPhone( $this->faker->optional($chancesOfValue = 0.5, $default = null)->text(20) );
            $user->setIsAdmin( $this->faker->boolean($chanceOfGettingTrue = 50) );
            $user->setIsActive( $this->faker->boolean($chanceOfGettingTrue = 50) );
            $user->setCreatedDate( $this->faker->dateTimeBetween($startDate = "- 12 months", $endDate = "- 3 months") );
            $user->setSchool( $this->faker->randomElement($allSchoolSites) );

            $this->doctrine->getManager()->persist($user);
            $this->progress->advance();
    }

        $this->doctrine->getManager()->flush();
    }

    /**
     * Charge les lieux bidons
     *
     * @param int $num
     */
    protected function loadLocations(int $num): void
    {
        $this->progress->setMessage("loading locations");
        $allCities = $this->doctrine->getRepository(City::class)->findAll();
        for($i=0; $i<$num; $i++){
            $location = new Location();

            $location->setName( $this->faker->name() );
            $location->setStreet( $this->faker->streetName );

            $city = $this->faker->randomElement($allCities);

            $location->setLatitude( $city->getLat() );
            $location->setLongitude( $city->getLng() );
            $location->setZip( $city->getZip() );
            $location->setCity( $city );

            $this->doctrine->getManager()->persist($location);
            $this->progress->advance();
    }

        $this->doctrine->getManager()->flush();
    }

    /**
     * Charge des événements bidons
     *
     * @param int $num
     * @throws \Exception
     */
    protected function loadEvents(int $num): void
    {
        $this->progress->setMessage("loading events");
        $allLocations = $this->doctrine->getRepository(Location::class)->findAll();
        $allEventStates = $this->doctrine->getRepository(EventState::class)->findAll();
        $allUsers = $this->doctrine->getRepository(User::class)->findAll();
        for($i=0; $i<$num; $i++){
            $event = new Event();

            $event->setName( $this->faker->catchPhrase );

            $event->setCreationDate( $this->faker->dateTimeBetween($startDate = "- 3 months") );
            $this->io->writeln($event->getCreationDate()->format("Y-m-d"));
            $event->setRegistrationLimitDate( $this->faker->dateTimeBetween($event->getCreationDate(), $event->getCreationDate()->add(new \DateInterval("P60D")) ));
            $event->setStartDate( $this->faker->dateTimeBetween($event->getRegistrationLimitDate(), $event->getRegistrationLimitDate()->add(new \DateInterval("P2D")) ));

            $event->setDuration( $this->faker->optional($chancesOfValue = 0.9, $default = null)->numberBetween($min = 1, $max = 6) );
            $event->setMaxRegistrations( $this->faker->optional($chancesOfValue = 0.5, $default = null)->numberBetween($min = 1000, $max = 9000) );
            $event->setInfos( $this->faker->paragraphs($nb = $this->faker->randomDigitNot(0), $asText = true) );
            $event->setLocation( $this->faker->randomElement($allLocations) );
            $event->setState( $this->faker->randomElement($allEventStates) );
            $event->setAuthor( $this->faker->randomElement($allUsers) );

            $this->doctrine->getManager()->persist($event);
            $this->progress->advance();
    }

        $this->doctrine->getManager()->flush();
    }


    /**
     * Inscrits des users à des sorties
     *
     * @param int $num
     * @throws \Exception
     */
    protected function loadEventSubscriptions(int $num): void
    {
        $this->progress->setMessage("loading eventsubscriptions");
        $allUsers = $this->doctrine->getRepository(User::class)->findAll();
        $allEvents = $this->doctrine->getRepository(Event::class)->findAll();
        for($i=0; $i<$num; $i++){
            $eventSubscription = new EventSubscription();

            $event = $this->faker->randomElement($allEvents);

            $eventSubscription->setCreatedDate( $this->faker->dateTimeBetween($event->getRegistrationLimitDate()->sub(new \DateInterval("P30D")), $event->getRegistrationLimitDate()) );
            $eventSubscription->setUser( $this->faker->randomElement($allUsers) );
            $eventSubscription->setEvent( $event );

            $this->doctrine->getManager()->persist($eventSubscription);
            $this->progress->advance();
    }

        $this->doctrine->getManager()->flush();
    }

    /**
     * Charge les raisons d'annulation
     */
    protected function loadEventCancelations(): void
    {
        $this->progress->setMessage("loading EventCancelations");
        $canceledState = $this->doctrine->getRepository(EventState::class)->findBy(['name' => 'canceled']);
        $canceledEvents = $this->doctrine->getRepository(Event::class)->findBy(['state' => $canceledState]);

        /** @var Event $event */
        foreach($canceledEvents as $event){
            $cancelationReason = new EventCancelation();
            $cancelationReason->setEvent($event);
            $cancelationReason->setReason($this->faker->paragraph);
            $cancelationReason->setCancelDate($this->faker->dateTimeBetween($event->getCreationDate(), $event->getCreationDate()->add(new \DateInterval("P20D"))));
            $this->doctrine->getManager()->persist($cancelationReason);
            $this->progress->advance();
            $this->doctrine->getManager()->flush();
        }
    }


    /**
     * Vide les tables (sauf ie cities)
     *
     * @throws \Exception
     */
    protected function truncateTables()
    {
        $this->progress->setMessage("Truncating tables");

        try {
            $connection = $this->doctrine->getConnection();
            $connection->beginTransaction();
            $connection->query("SET FOREIGN_KEY_CHECKS = 0");

            $connection->query("TRUNCATE school_site");
            $connection->query("TRUNCATE city");
            $connection->query("TRUNCATE user");
            $connection->query("TRUNCATE location");
            $connection->query("TRUNCATE event_state");
            $connection->query("TRUNCATE event");
            $connection->query("TRUNCATE event_subscription");
            $connection->query("TRUNCATE event_cancelation");

            $connection->query("SET FOREIGN_KEY_CHECKS = 1");
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}