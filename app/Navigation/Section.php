<?php

namespace App\Navigation;

use Spatie\Navigation\Section as SpatieSection;

class Section extends SpatieSection
{
    public function add(string $title = '', string $url = '', ?callable $configure = null, ?array $attributes = null): self
    {
        $section = new Section($this, $title, $url);

        if ($configure) {
            $configure($section);
        }

        if ($attributes) {
            $section->attributes($attributes);
        }

        $this->children[] = $section;

        return $this;
    }
}
