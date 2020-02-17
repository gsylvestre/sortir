<?php

namespace App\Tests;

class SmokeTest extends AbstractController
{
    /**
     * @dataProvider provideUrls
     */
    public function testPageShowSomethingIfConnected($url)
    {
        $this->logIn();
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful('response should be succesfull 200');
    }

    /**
     * @dataProvider provideUrls
     */
    public function testPageRedirectToLoginIfNotConnected($url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseRedirects("/connexion", 302, 'page should redirect if not connected');
    }

    public function provideUrls()
    {
        return [
            ["/"],
            ["/sorties"],
            ["/sorties/ajout"],
        ];
    }
}
