<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<array<string>>
     */
    public static function credentialProvider(): iterable
    {
        yield 'Admin' => ['ina@zaoui.com', 'password'];
        yield 'Visible guest' => ['invite+0@example.com', 'password'];
    }

    /**
     * @param string $email
     * @param string $password
     */
    #[DataProvider('credentialProvider')]
    public function testLoginPage(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $email;
        $form['_password'] = $password;

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/media');

        $this->client->followRedirect();
        self::assertSelectorTextContains('h1', 'Admin');
    }
}
