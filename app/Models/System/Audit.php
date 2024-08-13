<?php

namespace App\Models\System;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Events\AuditCustom;

/**
 * @mixin IdeHelperAudit
 */
class Audit extends \OwenIt\Auditing\Models\Audit
{
    use MassPrunable;

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable(): Builder
    {
        /** @var Builder */
        return static::where('created_at', '<=', now()->subYear());
    }

    /**
     * Create a custom audit.
     *
     * @param \OwenIt\Auditing\Contracts\Auditable $auditable
     * @param string                               $event
     * @param array<string, mixed>                 $new
     * @param array<string, mixed>                 $old
     *
     * @return void
     */
    public static function custom(Auditable $auditable, string $event, array $new, array $old = []): void
    {
        /** @var User $auditable */
        $auditable->auditEvent = $event;
        $auditable->isCustomEvent = true;
        $auditable->auditCustomOld = $old;
        $auditable->auditCustomNew = $new;
        Event::dispatch(AuditCustom::class, [$auditable]);
    }
}
