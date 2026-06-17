<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SecurityTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<array<string|null>>
     */
    public static function credentialProvider(): iterable
    {
        yield 'Admin' => ['ina@zaoui.com', 'password', '/admin/media'];
        yield 'Visible guest' => ['invite+0@example.com', 'password', '/admin/media'];
        yield 'Blocked guest' => ['invite+1@example.com', 'password', '/admin/media', 'forbidden'];
        yield 'Wrong email' => ['wrong@email.com', 'password', '/login', 'invalid'];
        yield 'Wrong password' => ['ina@zaoui.com', 'wrongpassword', '/login', 'invalid'];
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $path
     * @param string|null $case
     */
    #[DataProvider('credentialProvider')]
    public function testLoginPage(
        string $email,
        string $password,
        string $path,
        ?string $case = null
    ): void
    {
        $crawler = $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $email;
        $form['_password'] = $password;

        $this->client->submit($form);

        self::assertResponseRedirects($path);
        $this->client->followRedirect();

        switch ($case) {
            case 'forbidden':
                self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
                break;

            case 'invalid':
                self::assertSelectorTextContains('.alert-danger', 'Identifiants invalides.');
                break;

            default:
                self::assertSelectorTextContains('h1', 'Admin');
                break;
        }
    }
}
