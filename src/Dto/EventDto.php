<?php 

namespace App\Dto;

class EventDto extends Dto
{

    public function __construct(
        private int $id,
        private string $title,
        private ?string $start,
        private ?string $end,
        private ?string $description,
        private ?string $user,
        private ?string $state,
        private ?string $activity,
        private ?string $type,

    )
    {
    }

    public function getId() : int
    {
        return $this->id;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function getStart() : string
    {
        return $this->start;
    }
    public function getEnd() : string
    {
        return $this->end;
    }
    public function getDescription() : ?string
    {
        return $this->description;
    }
    public function getUser() : string
    {
        return $this->user;
    }
    public function getState() : string
    {
        return $this->state;
    }
    public function getActivity() : string
    {
        return $this->activity;
    }
    public function getType() : string
    {
        return $this->type;
    }
}
