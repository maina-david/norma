<?php

namespace App\Http\ResourceActions\Corpus;

use App\Models\Auth\User;

class ApplyDraftLegalDomains extends ApplyDraftTaggables
{
    protected function getTaggableRelation(): string
    {
        return 'legalDomains';
    }

    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.corpus.work-expression.apply-legal-domains');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'apply-legal-domain-drafts';
    }
}
