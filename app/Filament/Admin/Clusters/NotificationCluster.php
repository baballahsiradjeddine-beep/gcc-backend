<?php

namespace App\Filament\Admin\Clusters;

use App\Filament\Admin\AdminNavigation;
use Filament\Clusters\Cluster;

class NotificationCluster extends Cluster
{
    public static function getNavigationIcon(): ?string
    {
        return AdminNavigation::NOTIFICATION_CLUSTER['icon'];
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.nav.section.notifications');
    }

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::NOTIFICATION_CLUSTER['group']);
    }

    public static function getNavigationSort(): ?int
    {
        return AdminNavigation::NOTIFICATION_CLUSTER['sort'];
    }
}
