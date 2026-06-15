<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<array<string|null>>
     */
    public static function pageProvider(): iterable
    {
        yield 'Home' => ['/', 'Photographe', 'home-description', 'découvrir', 'Portfolio'];
        yield 'Guests' => ['/guests', 'Invités', null, 'découvrir', 'Invité 0'];
        yield 'First visible guest' => ['/guests/2', 'Invité 0'];
        yield 'Last visible guest' => ['/guests/100', 'Invité 98'];
        yield 'Portfolio' => ['/portfolio', 'Portfolio'];
        yield 'First album in the portfolio' => ['/portfolio/1', 'Portfolio'];
        yield 'Last album in the portfolio' => ['/portfolio/5', 'Portfolio'];
        yield 'About' => ['/about', 'Qui suis-je ?', 'about-description'];
        yield 'Login' => ['/login', 'Connexion'];
    }

    /**
     * @param string $path
     * @param string $title
     * @param string|null $expectedClass
     * @param string|null $linkText
     * @param string|null $newTitle
     */
    #[DataProvider('pageProvider')]
    public function testPage(
        string $path,
        string $title,
        ?string $expectedClass = null,
        ?string $linkText = null,
        ?string $newTitle = null
    ): void
    {
        $crawler = $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $title);

        if ($expectedClass !== null) {
            self::assertSelectorExists('.' . $expectedClass);
        }

        if ($linkText !== null && $newTitle !== null) {
            $link = $crawler->selectLink($linkText)->link();
            $this->client->click($link);

            self::assertSelectorTextContains('h1', $newTitle);
        }
    }
}
