<?php

namespace App\Filament\Resources\IpBlacklistResource\Pages;

use App\Filament\Resources\IpBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIpBlacklist extends CreateRecord
{
    protected static string $resource = IpBlacklistResource::class;
}
