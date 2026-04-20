<?php

namespace Modules\Blog\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class AuthorData extends Data
{
    public function __construct(
        public string $name,
        #[MapInputName('avatar')]
        public ?string $avatar_url = null,
    ) {}
}
