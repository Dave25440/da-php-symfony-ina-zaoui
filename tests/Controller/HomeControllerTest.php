<?php

namespace App\Tests\Controller;

use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private AlbumRepository $albumRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->albumRepository = static::getContainer()->get(AlbumRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
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
        yield 'Admin' => [1];
        yield 'First visible guest' => [2];
        yield 'First blocked guest' => [3];
        yield 'Last visible guest' => [100];
        yield 'Last blocked guest' => [101];
        yield 'Guest not found' => [102];
    }

    /**
     * @return iterable<array<int|null>>
     */
    public static function albumProvider(): iterable
    {
        yield 'No album' => [null];
        yield 'First album' => [1];
        yield 'Last album' => [5];
        yield 'Album not found' => [6];
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

        $expectedGuestCount = $this->userRepository->countByRole('ROLE_GUEST', true);
        $guests = $crawler->filter('.guest');

        if ($expectedGuestCount === 0) {
            self::assertCount(0, $guests);
            self::assertSelectorTextContains('p', 'Aucun invité disponible.');

            return;
        }

        self::assertCount($expectedGuestCount, $guests);

        $expectedGuests = $this->userRepository->findByRole('ROLE_GUEST', true);
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
        $crawler = $this->client->request('GET', '/guests/' . $id);

        if ($expectedGuest === null) {
            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

            return;
        }

        if (!in_array('ROLE_GUEST', $expectedGuest->getRoles(), true)) {
            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
            self::assertSelectorTextContains('body', 'Invité·e introuvable.');

            return;
        }

        self::assertResponseIsSuccessful();

        $guestName = $crawler->filter('h1')->text();
        $expectedDesc = $expectedGuest->getDescription() ?? '';
        $guestDesc = $crawler->filter('p')->text();

        self::assertSame($expectedGuest->getName(), $guestName);
        self::assertSame($expectedDesc, $guestDesc);

        $expectedMedias = $expectedGuest->getMedias();
        $expectedMediaCount = count($expectedMedias);
        $guestMedias = $crawler->filter('img.w-100');

        if ($expectedMediaCount === 0) {
            self::assertCount(0, $guestMedias);
            self::assertSelectorTextContains('p', 'Aucun média disponible.');

            return;
        }

        self::assertCount($expectedMediaCount, $guestMedias);

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

    /**
     * @param int|null $id
     */
    #[DataProvider('albumProvider')]
    public function testPortfolioPage(?int $id): void
    {
        $url = '/portfolio' . ($id !== null ? '/' . $id : '');
        $crawler = $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();

        $albums = $this->albumRepository->findAll();
        $albumsCount = max(1, count($albums) + 1);
        $buttons = $crawler->filter('a.btn');

        self::assertCount($albumsCount, $buttons);

        $albumIds = array_map(fn($album) => $album->getId(), $albums);
        $validId = $id !== null && in_array($id, $albumIds, true);

        $activeIndex = $validId ? ((int) array_search($id, $albumIds, true) + 1) : 0;
        $buttonClass = $buttons->eq($activeIndex)->attr('class');

        self::assertStringContainsString('active', (string) $buttonClass);

        $admin = $this->userRepository->findByRole('ROLE_ADMIN', true, 1);
        $admin = $admin[0] ?? null;
        $medias = $crawler->filter('img.w-100');

        if ($admin === null) {
            self::assertCount(0, $medias);
            self::assertSelectorTextContains('p', 'Aucun média disponible.');

            return;
        }

        if ($validId) {
            $album = $this->albumRepository->find($id);
            $expectedMedias = $album !== null ? $album->getMedias()->toArray() : [];
        } else {
            $expectedMedias = $admin->getMedias()->toArray();
        }

        $expectedMediaCount = count($expectedMedias);

        if ($expectedMediaCount === 0) {
            self::assertCount(0, $medias);
            self::assertSelectorTextContains('p', 'Aucun média disponible.');

            return;
        }

        self::assertCount($expectedMediaCount, $medias);

        $mediaPaths = $medias->each(fn($node) =>
            ltrim((string) $node->attr('src'), '/')
        );

        foreach ($expectedMedias as $i => $media) {
            self::assertSame(
                $media->getPath(),
                $mediaPaths[$i],
                "Le média #$i de {$admin->getName()} est correct."
            );
        }

        $expectedTitles = array_map(fn($media) => $media->getTitle(), $expectedMedias);
        $titles = $crawler->filter('p.media-title')->each(fn($node) => $node->text());

        foreach ($expectedTitles as $i => $title) {
            self::assertSame(
                $title,
                $titles[$i],
                "Le titre du média #$i de {$admin->getName()} est correct."
            );
        }
    }
}
