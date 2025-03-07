<?php
namespace App\Dto;

class MilestoneUserDto
{
    public function __construct(
        private string $firstName,
        private string $lastName,
    )
    {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string 
    {
        return $this->lastName;
    }
}
