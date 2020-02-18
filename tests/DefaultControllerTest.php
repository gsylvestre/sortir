<?php

namespace App\Tests;

class DefaultControllerTest extends AbstractController
{
    public function testHomeShowContent()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', "/");
        $this->assertResponseIsSuccessful('response should be succesfull 200');
        $this->assertSelectorTextContains('h4', 'sorties organisées');
    }
}
