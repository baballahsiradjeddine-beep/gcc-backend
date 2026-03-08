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
    
    public array $tito_qa_list; // array of {label: string, value: string} pairs

    public string $tito_app_goal;

    public string $tito_subscription_price;

    public string $tito_available_materials;

    public string $tito_social_links;

    public bool $tito_strict_mode; // To filter non-educational questions

    // Tool Images
    public string $tool_cards_grid;
    public string $tool_cards_list;
    public string $tool_resumes_grid;
    public string $tool_resumes_list;
    public string $tool_bac_solutions_grid;
    public string $tool_bac_solutions_list;
    public string $tool_pomodoro_grid;
    public string $tool_pomodoro_list;
    public string $tool_grade_calc_grid;
    public string $tool_grade_calc_list;
    public string $tool_ai_planner_grid;
    public string $tool_ai_planner_list;

    public static function group(): string
    {
        return 'default';
    }
}
