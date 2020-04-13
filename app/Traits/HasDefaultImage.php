<?php

namespace App\Traits;

trait HasDefaultImage
{
    public function getImage($altText) {
        if(!$this->logo){
            return "https://ui-avatars.com/api/?name=$altText&size=255";
        }
        return $this->logo;
    }
}