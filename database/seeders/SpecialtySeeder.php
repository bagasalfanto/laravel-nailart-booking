<?php

namespace Database\Seeders;

use App\Models\Nailist;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            '3D Art',
            'Minimalist',
            'Gel Extensions',
            'Luxury Embellishments',
            'Natural Care',
            'Korean Style',
            'French Classic',
            'Bridal Glam',
        ];

        $specialties = collect($defaults)->map(function (string $name) {
            return Specialty::updateOrCreate(
                ['name' => $name],
                ['slug' => Specialty::uniqueSlug($name), 'is_active' => true]
            );
        });

        // Sample assignment ke nailist yang ada (untuk demo).
        $samples = [
            'siti_nailist'  => ['Korean Style', '3D Art'],
            'rina_nailist'  => ['Minimalist', 'Gel Extensions'],
            'aulia_nailist' => ['Bridal Glam', 'Luxury Embellishments', 'Natural Care'],
        ];

        foreach ($samples as $username => $specialtyNames) {
            $nailist = Nailist::query()
                ->whereHas('user', fn ($q) => $q->where('username', $username))
                ->first();

            if (! $nailist) {
                continue;
            }

            $ids = $specialties->whereIn('name', $specialtyNames)->pluck('id')->all();
            $nailist->specialties()->sync($ids);
        }

        $this->command->info('Specialties seeded ('.$specialties->count().' items).');
    }
}
