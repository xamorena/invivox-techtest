<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReservationDTO
{
    public function __construct(
        #[Assert\NotBlank()]
        public readonly string $foodtruck,
        #[Assert\NotBlank()]
        #[Assert\DateTime()]
        public readonly string $date,
    )
    {
    }
}
