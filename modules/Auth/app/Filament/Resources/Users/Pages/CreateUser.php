<?php

namespace Modules\Auth\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Auth\Filament\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
