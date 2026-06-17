<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NavigationTest extends WebTestCase
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
        yield 'Portfolio' => ['/portfolio'];
        yield 'About' => ['/about'];
    }

    /**
     * @return iterable<array<string>>
     */
    public static function roleProvider(): iterable
    {
        yield 'Admin' => ['ROLE_ADMIN'];
        yield 'Guest' => ['ROLE_GUEST'];
        yield 'No role' => [''];
    }

    /**
     * @param string $path
     */
    #[DataProvider('pathProvider')]
    public function testNavigation(string $path): void
    {
        $crawler = $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();

        $homeLink = $crawler->filter('a[href="/"]');
        $navLinks = $crawler->filter('.nav-link');

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
     * @param string $role
     */
    #[DataProvider('roleProvider')]
    public function testNavigationForAuthenticatedUser(string $role): void
    {
        if ($role === '') {
            $user = $this->userRepository->findByRole('ROLE_GUEST', false, 1, 1);
            $user = $user[0] ?? null;
        } else {
            $user = $this->userRepository->findByRole($role, true, 1);
            $user = $user[0] ?? null;
        }

        self::assertNotNull($user);
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $homeLink = $crawler->filter('a[href="/"]');
        $navLinks = $crawler->filter('.nav-link');

        self::assertCount(1, $homeLink);
        self::assertCount(5, $navLinks);
        self::assertSelectorNotExists('a[href="/login"]');

        $expectedNav = [
            'Invités' => '/guests',
            'Portfolio' => '/portfolio',
            'Qui suis-je ?' => '/about',
            'Admin' => '/admin/media',
            'Déconnexion' => '/logout',
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
}
