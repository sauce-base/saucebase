<?php

namespace App\Navigation;

use Spatie\Navigation\Section as SpatieSection;

class Section extends SpatieSection
{
    public function add(string $title = '', string|\Closure $url = '', ?callable $configure = null, ?array $attributes = null): self
    {
        $section = new Section($this, $title, $url instanceof \Closure ? '' : $url);

        if ($configure) {
            $configure($section);
        }

        if ($url instanceof \Closure) {
            $section->attributes(['url_resolver' => $url]);
        }

        if ($attributes) {
            $section->attributes($attributes);
        }

        $this->children[] = $section;

        return $this;
    }
}
