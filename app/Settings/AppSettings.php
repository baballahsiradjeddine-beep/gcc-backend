<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    public string $app_version;

    public bool $resumes_active;

    public bool $bac_solutions_active;

    public bool $cards_tools_active;

    public string $payment_name;

    public string $payment_number;

    public bool $payment_active;

    public bool $chargily_payment_active;

    public string $tour_material_grid_image;

    public string $tour_material_list_image;

    public string $tour_unit_image;

    public string $tour_chapter_image;

    public static function group(): string
    {
        return 'default';
    }
}
