<?php


namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Cette class rend la méthode "login" dispo dans tous les contrôleurs !
 *
 * Class AbstractController
 * @package App\Tests
 */
abstract class AbstractController extends WebTestCase
{
    /** @var KernelBrowser|null  */
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $kernel = self::bootKernel();

        // returns the real and unchanged service container
        self::$container = $kernel->getContainer();
    }

    /**
     * Appeler cette fonction pour se connecter
     * Passer true pour se connecter en tant qu'admin
     * @param bool $asAdmin
     */
    protected function logIn(bool $asAdmin = false)
    {
        $session = self::$container->get('session');
        $session->clear();

        $firewallName = 'main';
        $firewallContext = 'main';

        if ($asAdmin){
            $user = self::$container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'admin@admin.com']);
            $token = new UsernamePasswordToken($user, null, $firewallName, ["ROLE_ADMIN"]);
        }
        else {
            $user = self::$container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'yo@yo.com']);
            $token = new UsernamePasswordToken($user, null, $firewallName, ["ROLE_USER"]);
        }
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $user;
    }
}