<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MediaTest extends TestCase
{
    public function testMedia(): void
    {
        $media = new Media();

        $album = new Album();
        $media->setAlbum($album);

        self::assertSame($album, $media->getAlbum());

        $user = new User();
        $media->setUser($user);

        self::assertSame($user, $media->getUser());

        $media->setPath('uploads/image.webp');
        self::assertSame('uploads/image.webp', $media->getPath());

        $media->setTitle('Média');
        self::assertSame('Média', $media->getTitle());

        $file = self::createStub(UploadedFile::class);
        $media->setFile($file);

        self::assertSame($file, $media->getFile());
    }
}
