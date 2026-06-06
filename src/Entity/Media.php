<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(name: 'user_id')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(name: 'album_id')]
    private ?Album $album = null;

    #[ORM\Column(name: 'path')]
    private string $path;

    #[ORM\Column(name: 'title')]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 2,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        max: 100,
        maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.'
    )]
    private string $title;

    #[Assert\NotNull(message: 'L\'image est obligatoire.')]
    #[Assert\File(
        maxSize: '2M',
        maxSizeMessage: 'La taille de l\'image ne doit pas dépasser {{ limit }} {{ suffix }}.',
        extensions: ['jpg', 'jpeg', 'png', 'webp'],
        extensionsMessage: 'L\'image doit être au format {{ extensions }}.'
    )]
    private ?UploadedFile $file = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): static
    {
        $this->file = $file;

        return $this;
    }
}
