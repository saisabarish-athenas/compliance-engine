<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ComplianceFullDummySeeder::class,
            ProductionComplianceMasterSeeder::class,
            MinimalRealisticDataSeeder::class,
            FullComplianceDemoSeeder::class,
        ]);
    }
}
