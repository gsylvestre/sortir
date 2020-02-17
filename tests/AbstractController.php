<?php


namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

    protected function logIn()
    {
        $session = self::$container->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $user = self::$container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'pif@pif.com']);
        $token = new UsernamePasswordToken($user, null, $firewallName, ["ROLE_USER"]);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}