<?php

namespace App\Http\ModelFilters\Auth;

use App\Enums\Auth\LifecycleStage;
use EloquentFilter\ModelFilter;

/**
 * @method static UserFilter emailLike(string $email)
 * @method static UserFilter nameLike(string $name)
 * @method static UserFilter inActive()
 * @method static UserFilter inStage(LifecycleStage $stage)
 */
class UserFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /* @phpstan-ignore-next-line */
        return $this->where(function ($q) use ($search) {
            $q->emailLike($search);
            $q->orWhere->nameLike($search);
        });
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function name(string $name): self
    {
        return $this->nameLike($name);
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function email(string $email): self
    {
        return $this->emailLike($email);
    }

    /**
     * @param string $active
     *
     * @return self
     */
    public function active(string $active): self
    {
        $active = (bool) $active;
        if ($active) {
            $this->query->active();
        } else {
            $this->inActive();
        }

        return $this;
    }

    /**
     * @param string $stage
     *
     * @return self
     */
    public function lifecycleStage(string $stage): self
    {
        $stage = (int) $stage;
        /** @var LifecycleStage $stage */
        $stage = LifecycleStage::fromValue($stage);
        $this->inStage($stage);

        return $this;
    }
}
