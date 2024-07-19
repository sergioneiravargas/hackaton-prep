<?php

declare(strict_types=1);

namespace App\Entity;

use Gedmo\SoftDeleteable\SoftDeleteable;
use Gedmo\Timestampable\Timestampable;

interface EntityInterface extends Timestampable, SoftDeleteable
{
}
