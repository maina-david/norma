<?php

namespace App\Imports\Customer\Settings;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Services\Auth\UserLifecycleService;
use App\Stores\Customer\OrganisationUserStore;
use App\Stores\Customer\TeamUserStore;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UserImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    /**
     * @param Organisation          $organisation
     * @param Collection<int, Team> $teams
     * @param UserLifecycleService  $lifecycleService
     * @param TeamUserStore         $teamStore
     * @param OrganisationUserStore $orgStore
     * @param Role|null             $role
     */
    public function __construct(
        protected Organisation $organisation,
        protected Collection $teams,
        protected UserLifecycleService $lifecycleService,
        protected TeamUserStore $teamStore,
        protected OrganisationUserStore $orgStore,
        protected ?Role $role
    ) {
    }

    /**
     * Use the collection to create users and attach as required.
     *
     * @param Collection<array<string, string>> $collection
     *
     * @return void
     */
    public function collection(Collection $collection): void
    {
        $newUsers = $collection->map(function ($row) {
            $user = $this->createUser($row);

            $this->lifecycleService->invite($user);

            return $user;
        });

        /** @var EloquentCollection<int, User> $users */
        $users = (new User())->newCollection();
        foreach ($newUsers as $user) {
            $users->add($user);
        }

        if ($this->role) {
            $users->each(function ($user) {
                try {
                    // @phpstan-ignore-next-line
                    $user->roles()->attach($this->role->id);
                } catch (Exception) {
                }
            });
        }

        $this->teams->each(function ($team) use ($users) {
            try {
                $this->teamStore->attachUsers($team, $users);
                // @codeCoverageIgnoreStart
            } catch (Exception) {
            }
            // @codeCoverageIgnoreEnd
        });

        $this->orgStore->attachUsers($this->organisation, $users);
    }

    /**
     * Create a new user for the given row.
     *
     * @param Collection<string,string> $row
     *
     * @return User
     */
    protected function createUser(Collection $row): User
    {
        /** @var User */
        return User::firstOrCreate(['email' => strtolower($row->get('email') ?? '')], [
            'fname' => ucwords(strtolower($row->get('first_name') ?? '')),
            'sname' => ucwords(strtolower($row->get('last_name') ?? '')),
            'email' => strtolower($row->get('email') ?? ''),
            'mobile_country_code' => $row->get('mobile_country_code'),
            'phone_mobile' => $row->get('mobile_phone'),
            'user_type' => UserType::customer()->value,
            'lifecycle_stage' => LifecycleStage::notOnboarded()->value,
        ]);
    }

    /**
     * Get the validation rules.
     *
     * @return string[][]
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            // for batches
            '*.email' => ['required', 'email', 'max:255', 'unique:' . User::class],
        ];
    }

    /**
     * @codeCoverageIgnore
     * Set the batch size.
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * @codeCoverageIgnore
     * Set the chunk import size.
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
