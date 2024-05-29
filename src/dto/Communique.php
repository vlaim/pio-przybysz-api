<?php

namespace vlaim\PioCheck\dto;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Communique extends Dto
{
    public int $id;
    protected string $application;
    protected string $title;
    protected string $content;
    protected bool $decision;
    protected string $sentAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getApplication(): string
    {
        return $this->application;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isDecision(): bool
    {
        return $this->decision;
    }

    public function getSentAt(): string
    {
        return $this->sentAt;
    }




}