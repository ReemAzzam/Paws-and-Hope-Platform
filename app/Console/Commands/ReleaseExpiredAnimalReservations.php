<?php

namespace App\Console\Commands;

use App\Models\Animal;
use Illuminate\Console\Command;

class ReleaseExpiredAnimalReservations extends Command
{
    protected $signature = 'animals:release-expired-reservations';

    protected $description = 'Release animal reservations that have expired';

    public function handle()
    {
        $count = Animal::where('availability_status', 'reserved')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->update([
                'availability_status' => 'available',
                'reserved_until' => null,
            ]);

        $this->info("Released {$count} expired animal reservations.");

        return self::SUCCESS;
    }
}