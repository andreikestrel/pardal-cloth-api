<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(
            [],
            [
                'company_name'    => 'Pardal Cloth',
                'primary_color'   => '#111827',
                'secondary_color' => '#374151',
                'accent_color'    => '#F59E0B',
            ]
        );
    }
}
