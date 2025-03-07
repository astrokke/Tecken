<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 14, nullable: true)]
    #[Assert\Length(
        exactly: 14,
        exactMessage: "Le numéro SIRET doit être composé de {{ limit }} charactères, vous avez inséré {{ value_length }} charactères",
    )]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        match: true,
        message: "Le numéro SIRET doit être composé uniquement de chiffres",
    )]
    private ?string $SIRET = null;

    #[ORM\Column(length: 9, nullable: true)]
    private ?string $SIREN = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $socialReason = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        match: true,
        message: "le numéro de téléphone doit être uniquement composé de chiffres",
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $webSite = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        match: true,
        message: "le numéro de téléphone doit être uniquement composé de chiffres",
    )]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 13, nullable: true)]
    #[Assert\Length(
        min: 4,
        minMessage: "Le code TVA doit faire plus de {{ limit }} charactères, vous avez inséré {{ value_length }} charactères",
        max: 15,
        maxMessage: "Le code TVA doit faire moins de {{ limit }} charactères, vous avez inséré {{ value_length }} charactères",
    )]
    #[Assert\Regex(
        pattern: '/^[A-Za-z]{2}[A-Za-z0-9]*$/',
        match: true,
        message: "Le code TVA doit être composé du code Pays sur deux lettres, suivie 2 à 13 charactères",
    )]
    private ?string $TVANumber = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'client')]
    private Collection $activities;

    /**
     * @var Collection<int, Interlocutor>
     */
    #[ORM\OneToMany(targetEntity: Interlocutor::class, mappedBy: 'client', orphanRemoval: true)]
    private Collection $interlocutors;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image = null;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->interlocutors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSIRET(): ?string
    {
        return $this->SIRET;
    }

    public function setSIRET(?string $SIRET): static
    {
        $this->SIRET = $SIRET;

        return $this;
    }

    public function getSIREN(): ?string
    {
        return $this->SIREN;
    }

    public function setSIREN(?string $SIREN): static
    {
        $this->SIREN = $SIREN;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getSocialReason(): ?string
    {
        return $this->socialReason;
    }

    public function setSocialReason(?string $socialReason): static
    {
        $this->socialReason = $socialReason;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getWebSite(): ?string
    {
        return $this->webSite;
    }

    public function setWebSite(?string $webSite): static
    {
        $this->webSite = $webSite;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getTVANumber(): ?string
    {
        return $this->TVANumber;
    }

    public function setTVANumber(?string $TVANumber): static
    {
        $this->TVANumber = $TVANumber;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->affiliated->contains($activity)) {
            $this->affiliated->add($activity);
            $activity->setClient($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {

            if ($activity->getClient() === $this) {
                $activity->setClient(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getSocialReason() ?? 'Client'; // Assure-toi que cette méthode retourne une chaîne
    }

    /**
     * @return Collection<int, Interlocutor>
     */
    public function getInterlocutors(): Collection
    {
        return $this->interlocutors;
    }

    public function addInterlocutor(Interlocutor $interlocutor): static
    {
        if (!$this->interlocutors->contains($interlocutor)) {
            $this->interlocutors->add($interlocutor);
            $interlocutor->setClient($this);
        }

        return $this;
    }

    public function removeInterlocutor(Interlocutor $interlocutor): static
    {
        if ($this->interlocutors->removeElement($interlocutor)) {
            // set the owning side to null (unless already changed)
            if ($interlocutor->getClient() === $this) {
                $interlocutor->setClient(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }
}

