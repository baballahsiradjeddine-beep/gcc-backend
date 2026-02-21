<?php

namespace App\Filament\Admin\Widgets;

use App\Services\Analytics\ReferralSourcesChartService;
use Filament\Widgets\ChartWidget;

class ReferralSourcesBarChart extends ChartWidget
{
    protected static ?string $maxHeight = '300px';
    protected int|string|array $columnSpan = 4;
    protected static ?int $sort = 1;
    public ?string $filter = 'year';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string
    {
        return __('custom.stats.referral_sources.widget.title');
    }

    protected function getFilters(): array
    {
        return [
            'today' => __('custom.stats.referral_sources.filter.today'),
            'week' => __('custom.stats.referral_sources.filter.week'),
            'month' => __('custom.stats.referral_sources.filter.month'),
            'year' => __('custom.stats.referral_sources.filter.year'),
            'all' => __('custom.stats.referral_sources.filter.all'),
        ];
    }

    protected function getData(): array
    {
        $service = new ReferralSourcesChartService();
        return $service->getChartData($this->filter);
    }
}
