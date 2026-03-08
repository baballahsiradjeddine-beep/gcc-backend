<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.tool_cards_grid', '');
        $this->migrator->add('default.tool_cards_list', '');
        $this->migrator->add('default.tool_resumes_grid', '');
        $this->migrator->add('default.tool_resumes_list', '');
        $this->migrator->add('default.tool_bac_solutions_grid', '');
        $this->migrator->add('default.tool_bac_solutions_list', '');
        $this->migrator->add('default.tool_pomodoro_grid', '');
        $this->migrator->add('default.tool_pomodoro_list', '');
        $this->migrator->add('default.tool_grade_calc_grid', '');
        $this->migrator->add('default.tool_grade_calc_list', '');
        $this->migrator->add('default.tool_ai_planner_grid', '');
        $this->migrator->add('default.tool_ai_planner_list', '');
    }
};
