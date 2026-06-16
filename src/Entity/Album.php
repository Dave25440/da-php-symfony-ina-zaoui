<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(name: 'name')]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 2,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        max: 100,
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.'
    )]
    private string $name;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'album', orphanRemoval: true)]
    private Collection $medias;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setAlbum($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getAlbum() === $this) {
                $media->setAlbum(null);
            }
        }

        return $this;
    }
}
