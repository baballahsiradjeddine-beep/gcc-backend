<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.tour_material_grid_image', '');
        $this->migrator->add('default.tour_material_list_image', '');
        $this->migrator->add('default.tour_unit_image', '');
        $this->migrator->add('default.tour_chapter_image', '');
    }
};
