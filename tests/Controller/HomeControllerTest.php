<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    /**
     * @return iterable<array<string>>
     */
    public static function pathProvider(): iterable
    {
        yield 'Home' => ['/'];
        yield 'Guests' => ['/guests'];
        yield 'First visible guest' => ['/guests/2'];
        yield 'Last visible guest' => ['/guests/100'];
        yield 'Portfolio' => ['/portfolio'];
        yield 'First album in the portfolio' => ['/portfolio/1'];
        yield 'Last album in the portfolio' => ['/portfolio/5'];
        yield 'About' => ['/about'];
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
     * @return iterable<array<int>>
     */
    public static function guestProvider(): iterable
    {
        yield 'First visible guest' => [2];
        yield 'Last visible guest' => [100];
    }

    /**
     * @param string $path
     */
    #[DataProvider('pathProvider')]
    public function testNavigation(string $path): void
    {
        $crawler = $this->client->request('GET', $path);
        $homeLink = $crawler->filter('a[href="/"]');
        $navLinks = $crawler->filter('.nav-link');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $homeLink);
        self::assertCount(4, $navLinks);

        self::assertSelectorExists(
            'a[href="/"] > img',
            "Le logo est présent dans le lien vers la page d'accueil."
        );

        $logoAlt = $homeLink->filter('img')->attr('alt');
        self::assertSame('Ina Zaoui', $logoAlt);

        $expectedNav = [
            'Invités' => '/guests',
            'Portfolio' => '/portfolio',
            'Qui suis-je ?' => '/about',
            'Connexion' => '/login',
        ];

        $navLabels = $navLinks->each(fn($node) => trim($node->text()));
        $navUrls = $navLinks->each(fn($node) => $node->attr('href'));

        $i = 0;

        foreach ($expectedNav as $label => $url) {
            self::assertSame(
                $label,
                $navLabels[$i],
                "Le lien '$label' est bien placé dans le menu de navigation."
            );

            self::assertSame(
                $url,
                $navUrls[$i],
                "Le lien '$label' renvoie vers '$url'."
            );

            $i++;
        }
    }

    /**
     * @param string $path
     * @param string $title
     * @param string|null $expectedClass
     * @param string|null $linkLabel
     * @param string|null $newTitle
     */
    #[DataProvider('pageProvider')]
    public function testPage(
        string $path,
        string $title,
        ?string $expectedClass = null,
        ?string $linkLabel = null,
        ?string $newTitle = null
    ): void
    {
        $crawler = $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $title);

        if ($expectedClass !== null) {
            self::assertSelectorExists('.' . $expectedClass);
        }

        if ($linkLabel !== null && $newTitle !== null) {
            $link = $crawler->selectLink($linkLabel)->link();
            $this->client->click($link);

            self::assertSelectorTextContains('h1', $newTitle);
        }
    }

    public function testGuestsPage(): void
    {
        $crawler = $this->client->request('GET', '/guests');
        self::assertResponseIsSuccessful();

        $expectedGuests = $this->userRepository->findByRole('ROLE_GUEST', true);
        $guests = $crawler->filter('.guest');

        self::assertCount(count($expectedGuests), $guests);

        $expectedNames = array_map(fn($guest) => $guest->getName(), $expectedGuests);
        $guestTitles = $guests->filter('h2');

        $guestNames = $guestTitles->each(function ($node) {
            // Extraction du nom de l'invité·e sans le nombre de médias
            return preg_replace('/\s*\(\d+\)$/', '', trim($node->text()));
        });

        self::assertSame($expectedNames, $guestNames);

        $expectedMediaCounts = array_map(
            fn($guest) => count($guest->getMedias()),
            $expectedGuests
        );

        $guestMediaCounts = $guestTitles->each(function ($node) {
            // Extraction du nombre de médias
            return (bool) preg_match('/\((\d+)\)$/', $node->text(), $matches)
                ? (int) $matches[1]
                : 0;
        });

        foreach ($expectedMediaCounts as $i => $expectedCount) {
            self::assertSame(
                $expectedCount,
                $guestMediaCounts[$i],
                "Le nombre de médias de {$guestNames[$i]} est correct."
            );
        }

        $guestCount = $guests->count();

        for ($i = 0; $i < $guestCount; $i++) {
            $guest = $guests->eq($i);
            self::assertCount(1, $guest->filter('a:contains("découvrir")'));
        }

        foreach ([0, $guestCount - 1] as $i) {
            $guest = $guests->eq($i);
            $link = $guest->selectLink('découvrir')->link();

            self::assertMatchesRegularExpression('/\/guests\/\d+$/', $link->getUri());

            $this->client->click($link);
            self::assertSelectorTextContains('h1', $guestNames[$i]);

            if ($i < $guestCount - 1) {
                $crawler = $this->client->request('GET', '/guests');
                $guests = $crawler->filter('.guest');
            }
        }
    }

    /**
     * @param int $id
     */
    #[DataProvider('guestProvider')]
    public function testGuestPage(int $id): void
    {
        $expectedGuest = $this->userRepository->find($id);
        self::assertNotNull($expectedGuest);

        $crawler = $this->client->request('GET', '/guests/' . $id);
        self::assertResponseIsSuccessful();

        $guestName = $crawler->filter('h1')->text();
        $expectedDesc = $expectedGuest->getDescription() ?? '';
        $guestDesc = $crawler->filter('p')->text();

        self::assertSame($expectedGuest->getName(), $guestName);
        self::assertSame($expectedDesc, $guestDesc);

        $expectedMedias = $expectedGuest->getMedias();
        $guestMedias = $crawler->filter('img.w-100');

        self::assertCount(count($expectedMedias), $guestMedias);

        $guestMediaPaths = $guestMedias->each(fn($node) =>
            ltrim((string) $node->attr('src'), '/')
        );

        foreach ($expectedMedias as $i => $media) {
            self::assertSame(
                $media->getPath(),
                $guestMediaPaths[$i],
                "Le média #$i de $guestName est correct."
            );
        }
    }
}
