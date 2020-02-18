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
     * @dataProvider provideBackOfficeUrls
     */
    public function testPageRedirectToLoginIfNotConnected($url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseRedirects("/connexion", 302, 'page should redirect if not connected');
    }

    /**
     * @dataProvider provideBackOfficeUrls
     */
    public function testBackOfficeIsLockedWithWrongRole($url)
    {
        $user = $this->login(false);

        $crawler = $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(403, "should be blocked");
    }

    /**
     * @dataProvider provideBackOfficeUrls
     */
    public function testBackOfficeIsUnlockedForAdmins($url)
    {
        $user = $this->login(true);

        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful('response should be succesfull 200');
    }


    public function provideUrls()
    {
        return [
            ["/"],
            ["/sorties"],
            ["/sorties/ajout"],
        ];
    }

    public function provideBackOfficeUrls()
    {
        return [
            ["/admin/utilisateurs/ajout"],
        ];
    }
}
