<?php

namespace App\Http\Controllers\System\Admin;

use App\Enums\System\SystemNotificationAlertType;
use App\Http\Controllers\Abstracts\Admin\AdminController;
use App\Http\Requests\System\SystemNotificationRequest;
use App\Models\System\SystemNotification;
use Illuminate\Http\Request;

class SystemNotificationController extends AdminController
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return SystemNotification::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'admin.system-notifications';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return SystemNotificationRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table. Use the dot notation for relations.
     * e.g. ['profile.id', 'name'].
     *
     * @return array<int|string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'id',
            'title',
            'type' => fn ($row) => SystemNotificationAlertType::lang()[$row['type']],
            'is_permanent' => fn ($row) => $row['is_permanent'] ? __('system.system_notification.yes') : '',
            'active' => fn ($row) => $row['active'] ? __('system.system_notification.yes') : '',
            'has_user_action' => fn ($row) => $row['has_user_action'] ? __('system.system_notification.yes') : '',
            'created_at',
        ];
    }
}
