<?php

namespace App\Tests;

class SmokeTest extends AbstractController
{
    /**
     * Teste que les pages de base affichent retourne un code HTTP 200 et que le body n'est pas vide
     *
     * Le dataprovider permet d'appeler automatiquement cette fonction, en passant tour à tour les données du tableau
     * de la fonction provideUrls
     *
     * @dataProvider provideUrls
     */
    public function testPageShowSomethingIfConnected($url)
    {
        //on se connecte ! voir le tests/AbstractController.php
        $this->logIn();

        //on fait une requête GET à l'URL reçue en argument
        $crawler = $this->client->request('GET', $url);

        //on teste qu'on reçoit un code 200
        $this->assertResponseIsSuccessful('response should be succesfull 200');

        $h2InBody = $crawler->filter("body h2");
        $this->assertCount(1, $h2InBody, "there should be one h2 in the body");
    }

    /**
     * Toutes les urls fournies par les provider devraient rediriger vers le login si pas connecté
     *
     * @dataProvider provideUrls
     * @dataProvider provideBackOfficeUrls
     */
    public function testPageRedirectToLoginIfNotConnected($url)
    {
        //note : on ne se connecte pas ici

        $crawler = $this->client->request('GET', $url);
        $this->assertResponseRedirects("/connexion", 302, 'page should redirect to login if not connected');
    }

    /**
     * Le back-office doit être blocké si on est connecté avec un simple user
     *
     * @dataProvider provideBackOfficeUrls
     */
    public function testBackOfficeIsLockedWithWrongRole($url)
    {
        //se connecte en simple user
        $user = $this->login(false);

        $crawler = $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(403, "should be blocked");
    }

    /**
     * Le back-office devrait s'afficher pour les admins
     *
     * @dataProvider provideBackOfficeUrls
     */
    public function testBackOfficeIsUnlockedForAdmins($url)
    {
        //se connecte en admin
        $user = $this->login(true);

        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful('response should be succesfull 200');
    }

    //fournit les urls de base du site
    //voir les annotation au-dessus des méthodes plus haut
    public function provideUrls()
    {
        return [
            ["/"],
            ["/sorties"],
            ["/sorties/details/67"],
            ["/sorties/ajout"],
            ["/profil/modification"],
            ["/profil/modification/photo"],
            ["/profil/12"],
        ];
    }

    //fournit les urls du back-office
    //voir les annotation au-dessus des méthodes plus haut
    public function provideBackOfficeUrls()
    {
        return [
            ["/admin/utilisateurs/ajout"],
            ["/admin/utilisateurs"],
            ["/admin"],
        ];
    }
}
