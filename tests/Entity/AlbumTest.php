<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Entity\Media;
use PHPUnit\Framework\TestCase;

final class AlbumTest extends TestCase
{
    public function testAlbum(): void
    {
        $album = new Album();
        $album->setName('Album');

        $media = new Media();
        $album->addMedia($media);

        self::assertCount(1, $album->getMedias());
        self::assertSame($album, $media->getAlbum());

        $album->removeMedia($media);

        self::assertCount(0, $album->getMedias());
        self::assertNull($media->getAlbum());
    }
}
