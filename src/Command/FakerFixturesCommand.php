<?php

namespace App\Command;

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
        $this->faker = \Faker\Factory::create("fr_FR");
        $this->doctrine = $doctrine;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this->setDescription('Load all fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $confirmed = $this->io->confirm('This will delete all your database datas. Continue?', false);
        if (!$confirmed){
            $this->io->text("Ok then.");
            return 0;
        }

        $this->progress = new ProgressIndicator($output);
        $this->progress->start('Loading fixtures');

        //empty all tables, reset ids
        $this->truncateTables();

        //order might be important
        //change argument to load more or less of each entity
        $this->loadUsers($num = 50);
        $this->loadLocations($num = 30);
        $this->loadEvents($num = 100);
        $this->loadEventSubscriptions($num = 500);

        //now loading ManyToMany data
        $this->progress->setMessage("loading many to many datas");
        $this->loadManyToManyData();

        $this->progress->finish("Done!");
        $this->io->success('Fixtures loaded!');
        return 0;
    }

    protected function loadUsers(int $num): void
    {
        $this->progress->setMessage("loading users");
        $allSchoolSites = $this->doctrine->getRepository(SchoolSite::class)->findAll();

        $yo = new User();
        $hash = $this->passwordEncoder->encodePassword($yo, "yoyoyo");
        $yo->setPassword($hash);
        $yo->setLastname( "yo" );
        $yo->setFirstname( "yo" );
        $yo->setPhone( "0601020304" );
        $yo->setEmail("yo@yo.com");
        $yo->setIsAdmin( 1 );
        $yo->setIsActive( 1 );
        $yo->setCreatedDate( new \DateTime() );
        $yo->setSchool( $this->faker->randomElement($allSchoolSites) );

        $this->doctrine->getManager()->persist($yo);

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

    protected function loadEvents(int $num): void
    {
        $this->progress->setMessage("loading events");
        $allLocations = $this->doctrine->getRepository(Location::class)->findAll();
        $allEventStates = $this->doctrine->getRepository(EventState::class)->findAll();
        $allUsers = $this->doctrine->getRepository(User::class)->findAll();
        for($i=0; $i<$num; $i++){
            $event = new Event();

            $event->setName( $this->faker->catchPhrase );
            $event->setStartDate( $this->faker->dateTimeBetween($startDate = "- 3 months", $endDate = "+ 6 months") );
            $event->setDuration( $this->faker->optional($chancesOfValue = 0.9, $default = null)->numberBetween($min = 1, $max = 6) );
            $event->setRegistrationLimitDate( $this->faker->dateTimeBetween($event->getStartDate()->sub(new \DateInterval('P30D')), $event->getStartDate()) );
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


    protected function truncateTables()
    {
        $this->progress->setMessage("Truncating tables");

        try {
            $connection = $this->doctrine->getConnection();
            $connection->beginTransaction();
            $connection->query("SET FOREIGN_KEY_CHECKS = 0");

            $connection->query("TRUNCATE user");
            $connection->query("TRUNCATE location");
            $connection->query("TRUNCATE event");
            $connection->query("TRUNCATE event_subscription");

            $connection->query("SET FOREIGN_KEY_CHECKS = 1");
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    protected function loadManyToManyData()
    {

    }
}