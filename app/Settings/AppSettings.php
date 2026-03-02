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
    
    public bool $tito_active;

    public string $tito_persona;

    public string $tito_welcome_message;

    public string $tito_api_key;
    
    public string $tito_qa_list; // JSON array of questions

    public string $tito_app_goal;

    public string $tito_subscription_price;

    public string $tito_available_materials;

    public string $tito_social_links;

    public bool $tito_strict_mode; // To filter non-educational questions

    public static function group(): string
    {
        return 'default';
    }
}
