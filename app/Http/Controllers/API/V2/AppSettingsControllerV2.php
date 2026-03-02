<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\API\BaseController;
use App\Settings\AppSettings;
use Dedoc\Scramble\Attributes\Group;

use Illuminate\Http\Request;

#[Group('App Settings APIs', weight: 4)]
class AppSettingsControllerV2 extends BaseController
{
    /**
     * Get app settings.
     *
     * This endpoint returns an object containing app settings.
     */
    public function index()
    {
        return $this->sendResponse([
            'app_version' => app(AppSettings::class)->app_version,
            'resumes_active' => app(AppSettings::class)->resumes_active,
            'bac_solutions_active' => app(AppSettings::class)->bac_solutions_active,
            'cards_tools_active' => app(AppSettings::class)->cards_tools_active,
            'payment_name' => app(AppSettings::class)->payment_name,
            'payment_number' => app(AppSettings::class)->payment_number,
            'payment_active' => app(AppSettings::class)->payment_active,
            'chargily_payment_active' => app(AppSettings::class)->chargily_payment_active,
            'tour_material_grid_image' => app(AppSettings::class)->tour_material_grid_image,
            'tour_material_list_image' => app(AppSettings::class)->tour_material_list_image,
            'tour_unit_image' => app(AppSettings::class)->tour_unit_image,
            'tour_chapter_image' => app(AppSettings::class)->tour_chapter_image,
            'tito_active' => app(AppSettings::class)->tito_active,
            'tito_persona' => app(AppSettings::class)->tito_persona,
            'tito_welcome_message' => app(AppSettings::class)->tito_welcome_message,
            'tito_api_key' => app(AppSettings::class)->tito_api_key,
        ]);
    }

    /**
     * Get app settings (structured).
     *
     * This endpoint returns an object containing app settings, same content as the the previous endpoint, but app variables and payment variables are separated.
     */
    public function separated()
    {
        return $this->sendResponse([
            'app' => [
                'app_version' => app(AppSettings::class)->app_version,
                'resumes_active' => app(AppSettings::class)->resumes_active,
                'bac_solutions_active' => app(AppSettings::class)->bac_solutions_active,
                'cards_tools_active' => app(AppSettings::class)->cards_tools_active,
            ],
            'payment' => [
                'payment_name' => app(AppSettings::class)->payment_name,
                'payment_number' => app(AppSettings::class)->payment_number,
                'payment_active' => app(AppSettings::class)->payment_active,
                'chargily_payment_active' => app(AppSettings::class)->chargily_payment_active,
            ],
            'tour' => [
                'tour_material_grid_image' => app(AppSettings::class)->tour_material_grid_image,
                'tour_material_list_image' => app(AppSettings::class)->tour_material_list_image,
                'tour_unit_image' => app(AppSettings::class)->tour_unit_image,
                'tour_chapter_image' => app(AppSettings::class)->tour_chapter_image,
            ],
            'tito' => [
                'tito_active' => app(AppSettings::class)->tito_active,
                'tito_persona' => app(AppSettings::class)->tito_persona,
                'tito_welcome_message' => app(AppSettings::class)->tito_welcome_message,
                'tito_api_key' => app(AppSettings::class)->tito_api_key,
                'tito_qa_list' => is_string(app(AppSettings::class)->tito_qa_list) ? json_decode(app(AppSettings::class)->tito_qa_list, true) : app(AppSettings::class)->tito_qa_list,
                'tito_app_goal' => app(AppSettings::class)->tito_app_goal,
                'tito_subscription_price' => app(AppSettings::class)->tito_subscription_price,
                'tito_available_materials' => app(AppSettings::class)->tito_available_materials,
                'tito_social_links' => app(AppSettings::class)->tito_social_links,
                'tito_strict_mode' => app(AppSettings::class)->tito_strict_mode,
            ]
        ]);
    }
}
