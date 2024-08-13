<?php

namespace App\Http\Services\Hubspot\Actions;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserActivityType;
use App\Enums\Auth\UserType;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Notify\Pivots\LegalUpdateUser;
use Carbon\Carbon;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use Illuminate\Database\Eloquent\Builder;

class UserToHubspotContact
{
    /**
     * @param User $user
     */
    private function __construct(private User $user)
    {
    }

    /**
     * Convert the user to a hubspot property collection.
     *
     * @param User $user
     *
     * @return SimplePublicObjectInput
     */
    public static function convert(User $user): SimplePublicObjectInput
    {
        if (self::shouldEagerLoad($user)) {
            /** @var User $user */
            $user = self::eagerLoadFields(User::where('id', $user->id))->firstOrFail();
        }

        return (new self($user))->toHubspotContact();
    }

    /**
     * Check if the user should be re-fetched and eager loaded.
     *
     * @param User $user
     *
     * @return bool
     */
    protected static function shouldEagerLoad(User $user): bool
    {
        return collect([
            'all_activities_count', 'months_activities_count', 'year_activities_count', 'search_activity_count',
            'compliance_activity_count', 'know_your_law_activity_count', 'update_activity_count',
            'successful_searches_count',
        ])
            ->filter(fn ($field) => !isset($user->{$field}))
            ->count() > 0;
    }

    /**
     * Update the Query Builder to include required extra fields.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public static function eagerLoadFields(Builder $builder): Builder
    {
        $legalUpdatePivot = (new LegalUpdateUser())->getTable();
        $activities = (new UserActivity())->getTable();
        $users = (new User())->getTable();
        $firstLastQuery = UserActivity::whereColumn("{$activities}.user_id", "{$users}.id")
            ->take(1)
            ->select(['created_at']);

        return $builder
            ->addSelect([
                'first_activity' => $firstLastQuery->clone()->orderBy('id'),
                'last_activity' => $firstLastQuery->clone()->orderBy('id', 'desc'),
            ])
            ->with([
                'hubspot',
                'activities' => function ($builder) {
                    $builder->orderBy('id', 'desc')
                        ->select(['user_id', 'created_at'])
                        ->where('created_at', '>=', Carbon::now()->startOfYear());
                },
                'roles' => function ($builder) {
                    $builder->select(['id', 'title', 'app']);
                },
                'teams' => function ($builder) {
                    $builder->select(['id', 'title']);
                },
                'organisations' => function ($builder) {
                    $builder->select(['id', 'whitelabel_id', 'title', 'version']);
                },
                'organisations.whitelabel' => function ($builder) {
                    $builder->select(['id', 'title']);
                },
            ])
            ->withCount([
                'activities as all_activities_count',
                'activities as months_activities_count' => function ($query) {
                    $query->where('created_at', '>=', Carbon::now()->startOfMonth());
                },
                'activities as year_activities_count' => function ($query) {
                    $query->where('created_at', '>=', Carbon::now()->startOfYear());
                },
                'activities as search_activity_count' => function ($query) {
                    $query->where('created_at', '>=', Carbon::now()->startOfYear())->searchActivity();
                },
                'activities as compliance_activity_count' => function ($query) {
                    $query->where('created_at', '>=', Carbon::now()->startOfYear())->complianceActivity();
                },
                'activities as know_your_law_activity_count' => function ($query) {
                    $query->where('created_at', '>=', Carbon::now()->startOfYear())->knowYourLawActivity();
                },
                'activities as update_activity_count' => function ($query) {
                    $query->where('created_at', '>=', Carbon::now()->startOfYear())->updateActivity();
                },
                'activities as successful_searches_count' => function ($query) {
                    $query->where('activity_type', UserActivityType::searchTerm()->value)
                        ->where('created_at', '>=', Carbon::now()->startOfYear());
                },
                'unreadLegalUpdates',
                'legalUpdates',
                'legalUpdates as only_read_updates_count' => function ($query) use ($legalUpdatePivot) {
                    $query->where("{$legalUpdatePivot}.read_status", true)
                        ->where("{$legalUpdatePivot}.understood_status", false);
                },
                'legalUpdates as understood_updates_count' => function ($query) use ($legalUpdatePivot) {
                    $query->where("{$legalUpdatePivot}.read_status", true)
                        ->where("{$legalUpdatePivot}.understood_status", true);
                },
            ])
            ->withCasts([
                'first_activity' => 'datetime',
                'last_activity' => 'datetime',
            ]);
    }

    /**
     * Convert the object to a collection of hubspot contact properties.
     *
     * @return SimplePublicObjectInput
     */
    public function toHubspotContact(): SimplePublicObjectInput
    {
        $fields = collect([
            'original_email' => $this->user->getOriginal('email'),
            'email' => $this->user->email,
            'from_libryo_api' => 'Yes',
            'firstname' => $this->user->fname,
            'lastname' => $this->user->sname,
            'phone' => "{$this->user->mobile_country_code} {$this->user->phone_mobile}",
            'libryo_status' => $this->user->active ? 'active' : 'inactive',
            'legal_updates_unread' => $this->user->unread_legal_updates_count,
            'total_number_of_legal_updates' => $this->user->legal_updates_count,
            'legal_updates_read' => $this->user->only_read_updates_count, // @phpstan-ignore-line
            'legal_updates_read_and_understood' => $this->user->understood_updates_count, // @phpstan-ignore-line
            'libryo_know_your_law_activities' => $this->user->know_your_law_activity_count, // @phpstan-ignore-line
            'successful_searches' => $this->user->successful_searches_count, // @phpstan-ignore-line
            'number_of_activities_all_time_' => $this->user->all_activities_count, // @phpstan-ignore-line
            'number_of_activities_yearly_' => $this->user->year_activities_count, // @phpstan-ignore-line
            'number_of_activities' => $this->user->months_activities_count, // @phpstan-ignore-line
            'user_type' => $this->getUserType(),
            'lifecyclestage' => $this->getLifeCycleStage(),
            'libryo_user_status' => $this->getUserStatus(),
            'journey_to_core' => $this->getCoreStatus(),
            'covid_19_campaign_rubicon' => $this->isRubiconFreeUser(),
            'free_user' => UserType::free()->is($this->user->user_type) ? 'Yes' : 'No',
            'date_added_to_libryo' => $this->toHubspotTimestamp($this->user->created_at),
            'first_activity_date' => $this->toHubspotTimestamp($this->user->first_activity), // @phpstan-ignore-line
            'libryo_organisation' => $this->user->organisations->implode('title', "\n"),
            'libryo_team' => $this->user->teams->implode('title', "\n"),
        ])
            ->filter(fn ($value) => !is_null($value) && strlen($value) > 0)
            ->merge($this->getComputedFields())
            ->toArray();

        return (new SimplePublicObjectInput())->setProperties($fields);
    }

    /**
     * Get the computed fields.
     *
     * @return array<string, mixed>
     */
    protected function getComputedFields(): array
    {
        $versions = $this->user->organisations->pluck('version')->unique()->reject(fn ($record) => is_null($record))
            ->sort()
            ->map(fn ($record) => 'Version ' . $record)
            ->implode(', ');

        $libryoScore = 100;

        if ($this->user->legal_updates_count > 0) {
            $readScore = $this->user->only_read_updates_count + (2 * $this->user->understood_updates_count); // @phpstan-ignore-line
            $totalScore = 2 * $this->user->legal_updates_count;
            $libryoScore = round(($readScore / $totalScore) * 100);
        }

        $activityRanking = $this->getActivityRanking();

        $fields = [
            'libryo_score' => $libryoScore,
            'libryo_search' => $activityRanking[0],
            'libryo_compliance' => $activityRanking[1],
            'libryo_know_your_law' => $activityRanking[2],
            'libryo_updates' => $activityRanking[3],
        ];

        // @codeCoverageIgnoreStart
        if (strlen($versions) > 2) {
            $fields['libryo_version'] = $versions;
        }
        // @codeCoverageIgnoreEnd

        if ($this->user->last_activity) { // @phpstan-ignore-line
            $fields['last_activity'] = $this->user->last_activity->format('d/m/Y');
            $fields['recently_active'] = Carbon::now()->subMonth()->lte($this->user->last_activity) ? 1 : 0;
        }

        return $fields;
    }

    /**
     * Convert the parameter to a hubspot timestamp.
     *
     * @param mixed $time
     *
     * @return int|null
     */
    protected function toHubspotTimestamp(mixed $time): ?int
    {
        if ($time) {
            return Carbon::parse($time)->startOfDay()->getTimestamp() * 1000;
        }

        return null;
    }

    /**
     * Get the Hubspot User Type.
     *
     * @return string
     */
    protected function getUserType(): string
    {
        return match ($this->user->user_type) {
            UserType::demo()->value => 'Demo User',
            UserType::other()->value => 'Other',
            UserType::partner()->value => 'Partner',
            UserType::staff()->value => 'Staff',
            default => 'User',
        };
    }

    /**
     * Get the user's lifecycle stage.
     *
     * @return string
     */
    protected function getLifeCycleStage(): string
    {
        return match ($this->user->user_type) {
            UserType::free()->value => 'lead',
            UserType::partner()->value => 'other',
            default => 'customer',
        };
    }

    /**
     * Map the user lifecycle stage to the hubspot user status.
     *
     * @return string
     */
    protected function getUserStatus(): string
    {
        return match ($this->user->lifecycle_stage) {
            LifecycleStage::deactivated()->value => 'Deactivated',
            LifecycleStage::deactivatedInactivity()->value => 'Deactivated_inactivity',
            LifecycleStage::invitationDeclined()->value => 'Invitation_declined',
            LifecycleStage::invitationSent()->value => 'Invitation_sent',
            LifecycleStage::notOnboarded()->value => 'Not Onboarded',
            default => 'Active',
        };
    }

    /**
     * Map the user activities count to the core status.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function getCoreStatus(): string
    {
        if ($this->user->all_activities_count > 200) { // @phpstan-ignore-line
            return 'Core Plus';
        }
        if ($this->user->all_activities_count > 100) {
            return 'Core';
        }
        if ($this->user->all_activities_count > 80) {
            return 'Engaged';
        }
        if ($this->user->all_activities_count > 50) {
            return 'Medium Engaged';
        }
        if ($this->user->all_activities_count > 30) {
            return 'Low Activity';
        }
        if ($this->user->all_activities_count > 10) {
            return 'Very Low Activity';
        }

        return 'Disengaged';
    }

    /**
     * Check if it's a Rubicon Free User.
     *
     * @return string
     */
    protected function isRubiconFreeUser(): string
    {
        if (UserType::free()->is($this->user->user_type) && $this->user->organisations->count() === 1) {
            $whitelabel = $this->user->organisations->first()->whitelabel->title ?? null;

            return $whitelabel === 'Rubicon' ? 'Yes' : 'No';
        }

        return 'No';
    }

    /**
     * Get an array ranking the user activities by category
     * 0 = no activities
     * 1 is the highest rank.
     *
     * @return array<int, int>
     */
    protected function getActivityRanking(): array
    {
        $countArray = [
            $this->user->search_activity_count, // @phpstan-ignore-line
            $this->user->compliance_activity_count, // @phpstan-ignore-line
            $this->user->know_your_law_activity_count, // @phpstan-ignore-line
            $this->user->update_activity_count, // @phpstan-ignore-line
        ];

        $orderedValues = $countArray;
        rsort($orderedValues);

        $ranking = [];

        foreach ($countArray as $key => $value) {
            if ($value === 0) {
                $ranking[] = 0;
                continue;
            }

            // @codeCoverageIgnoreStart
            foreach ($orderedValues as $orderedKey => $orderedValue) {
                if ($value === $orderedValue) {
                    $key = $orderedKey;

                    break;
                }
            }

            $ranking[] = $key + 1;
            // @codeCoverageIgnoreEnd
        }

        return $ranking;
    }
}
