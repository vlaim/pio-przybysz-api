<?php

declare(strict_types=1);


namespace vlaim\PioCheck\dto;


/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Application extends Dto
{
    protected int $stage;
    protected int $kind;
    protected int $status;
    protected string $number;
    protected string $caseEzdNumber;
    protected string $caseOwner;
    protected string $personSystemNumber;
    protected string $serializeForm;
    protected array $communiques;

    /**
     * @return int
     */
    public function getKind(): int
    {
        return $this->kind;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getCaseEzdNumber(): string
    {
        return $this->caseEzdNumber;
    }

    /**
     * @return string
     */
    public function getCaseOwner(): string
    {
        return $this->caseOwner;
    }

    /**
     * @return string
     */
    public function getPersonSystemNumber(): string
    {
        return $this->personSystemNumber;
    }

    public function getStage(): int
    {
        return $this->stage;
    }


    public function getStageName(): string
    {
        return match ($this->stage) {
            1 => 'Rejestracja wniosku',
            2 => 'Przyjęto w Urzędzie',
            3 => 'Wyznaczono inspektora',
            4 => 'Weryfikacja wniosku',
            5 => 'Pismo w sprawie – braki formalne',
            6 => 'Pismo w sprawie – wszczęcie postępowania',
            7 => 'Ponowne wezwanie',
            9 => 'Postępowanie w toku',
            10 => 'Projekt decyzji',
            11 => 'Zakończenie postępowania',
            12 => 'Zlecenie personalizacji karty pobytu',
            13 => 'Karta pobytu do odbioru',
            16 => 'Zawieszenie postępowania z urzędu',
            default => '?',
        };

    }





}