<?php declare(strict_types=1);

namespace App\Util;

use Cocur\Slugify\Slugify;
use stdClass;

final class EventUrlGenerator
{
    private Slugify $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function getUrl(stdClass $event): string
    {
        return sprintf(
            "https://simss.pl/seria/%s/%s-%s",
            $event->relatedleague->slug,
            $event->id,
            $this->slugify->slugify($event->name)
        );
    }
}