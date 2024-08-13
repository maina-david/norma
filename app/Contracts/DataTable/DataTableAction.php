<?php

namespace App\Contracts\DataTable;

use App\Models\Auth\User;
use Illuminate\View\View;

interface DataTableAction
{
    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool;

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string;

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function id(): string;

    /**
     * Return the rendered view.
     *
     * @return \Illuminate\View\View|null
     */
    public function formView(): ?View;

    /**
     * Handle the action after it has been triggered.
     *
     * @param array<int, int|string> $resourceIds
     * @param array<string, mixed>   $payload
     * @param callable|null          $dispatch
     *
     * @return mixed
     */
    public function trigger(array $resourceIds, array $payload = [], ?callable $dispatch = null): mixed;
}
