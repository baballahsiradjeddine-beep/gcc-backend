<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Settings\AppSettings;
use Dedoc\Scramble\Attributes\Group;

#[Group('App Settings APIs', weight: 8)]
class AppSettingsController extends BaseController
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
            'tito_active' => app(AppSettings::class)->tito_active,
            'tito_persona' => app(AppSettings::class)->tito_persona,
            'tito_welcome_message' => app(AppSettings::class)->tito_welcome_message,
            'tito_api_key' => app(AppSettings::class)->tito_api_key,
            
            // Tool Images
            'tool_cards_grid' => app(AppSettings::class)->tool_cards_grid,
            'tool_cards_list' => app(AppSettings::class)->tool_cards_list,
            'tool_resumes_grid' => app(AppSettings::class)->tool_resumes_grid,
            'tool_resumes_list' => app(AppSettings::class)->tool_resumes_list,
            'tool_bac_solutions_grid' => app(AppSettings::class)->tool_bac_solutions_grid,
            'tool_bac_solutions_list' => app(AppSettings::class)->tool_bac_solutions_list,
            'tool_pomodoro_grid' => app(AppSettings::class)->tool_pomodoro_grid,
            'tool_pomodoro_list' => app(AppSettings::class)->tool_pomodoro_list,
            'tool_grade_calc_grid' => app(AppSettings::class)->tool_grade_calc_grid,
            'tool_grade_calc_list' => app(AppSettings::class)->tool_grade_calc_list,
            'tool_ai_planner_grid' => app(AppSettings::class)->tool_ai_planner_grid,
            'tool_ai_planner_list' => app(AppSettings::class)->tool_ai_planner_list,
        ]);
    }
}
