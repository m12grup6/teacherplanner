<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testAltaUsuari(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        
        // Obtenim el btó de registre per identificar el form
        $buttonCrawlerNode = $crawler->selectButton('Registre');
        
        // Obtenim el formulari com a objecte
        $form = $buttonCrawlerNode->form();
        
        // Introduim dades exemple
        $client->submit($form, [
            'user[name]'    => 'Pep',
            'user[password]' => 'Primera1',
            'user[email]' => 'hola@hola.com',
        ]);

        // comprovem que el resultat és ok
        $this->assertResponseIsSuccessful();
    }
    
    public function testLogin(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('hola@hola.com');

        $client->loginUser($testUser);

        $client->request('GET', '/courses');
        $this->assertResponseIsSuccessful();
    }
    
}
