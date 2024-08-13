<?php

namespace App\Http\Resources\Internals\Collaborate\Corpus;

use App\Http\Resources\Internals\Collaborate\ActionAreas\ActionAreaResource;
use App\Http\Resources\Internals\Collaborate\Assess\AssessmentItemResource;
use App\Http\Resources\Internals\Collaborate\Compilation\ContextQuestionResource;
use App\Http\Resources\Internals\Collaborate\Geonames\LocationResource;
use App\Http\Resources\Internals\Collaborate\Ontology\CategoryResource;
use App\Http\Resources\Internals\Collaborate\Ontology\LegalDomainResource;
use App\Http\Resources\Internals\Collaborate\Ontology\TagResource;
use App\Http\Resources\Internals\Collaborate\Requirements\SummaryResource;
use App\Models\Corpus\Reference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Reference */
class ReferenceResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'volume' => $this->volume,
            'level' => $this->level,
            'start' => $this->start,
            'status' => $this->status,
            'parent_id' => $this->parent_id,
            'work_id' => $this->work_id,
            'position' => $this->position,
            'is_section' => $this->is_section,
            'has_linked_toc' => !is_null($this->uid),
            'action_area_drafts_count' => $this->whenCounted('actionAreaDrafts', $this->action_area_drafts_count),
            'action_areas_count' => $this->whenCounted('actionAreas', $this->action_areas_count),
            'assessment_item_drafts_count' => $this->whenCounted('assessmentItemDrafts', $this->assessment_item_drafts_count),
            'assessment_items_count' => $this->whenCounted('assessmentItems', $this->assessment_items_count),
            'categories_count' => $this->whenCounted('categories', $this->categories_count),
            'category_drafts_count' => $this->whenCounted('categoryDrafts', $this->category_drafts_count),
            'collaborate_comments_count' => $this->whenCounted('collaborateComments', $this->collaborate_comments_count),
            'comments_count' => $this->whenCounted('comments', $this->comments_count),
            'content_draft' => new ReferenceContentDraftResource($this->whenLoaded('contentDraft')),
            'context_question_drafts_count' => $this->whenCounted('contextQuestionDrafts', $this->context_question_drafts_count),
            'context_questions_count' => $this->whenCounted('contextQuestions', $this->context_questions_count),
            'legal_domain_drafts_count' => $this->whenCounted('legalDomainDrafts', $this->legal_domain_drafts_count),
            'legal_domains_count' => $this->whenCounted('legalDomains', $this->legal_domains_count),
            'linked_children_count' => $this->whenCounted('linkedChildren', $this->linked_children_count),
            'linked_parents_count' => $this->whenCounted('linkedParents', $this->linked_parents_count),
            'location_drafts_count' => $this->whenCounted('locationDrafts', $this->location_drafts_count),
            'locations_count' => $this->whenCounted('locations', $this->locations_count),
            'tag_drafts_count' => $this->whenCounted('tagDrafts', $this->tag_drafts_count),
            'tags_count' => $this->whenCounted('tags', $this->tags_count),
            'reference_content_extracts_count' => $this->whenCounted('contentExtracts'),
            'requirement_count' => $this->whenCounted('refRequirement', $this->ref_requirement_count),
            'requirement_draft_count' => $this->whenCounted('requirementDraft', $this->requirement_draft_count),
            'requirement_draft_type' => $this->whenLoaded('requirementDraft', fn () => $this->requirementDraft->change_status ?? null),
            'summary_count' => $this->whenHas('summary_count', $this->summary_count),
            'summary_draft_count' => $this->whenHas('summary_draft_count', $this->summary_draft_count),
            'action_area_drafts' => ActionAreaResource::collection($this->whenLoaded('actionAreaDrafts')),
            'action_areas' => ActionAreaResource::collection($this->whenLoaded('actionAreas')),
            'assessment_item_drafts' => AssessmentItemResource::collection($this->whenLoaded('assessmentItemDrafts')),
            'assessment_items' => AssessmentItemResource::collection($this->whenLoaded('assessmentItems')),
            'category_drafts' => CategoryResource::collection($this->whenLoaded('categoryDrafts')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'context_question_drafts' => ContextQuestionResource::collection($this->whenLoaded('contextQuestionDrafts')),
            'context_questions' => ContextQuestionResource::collection($this->whenLoaded('contextQuestions')),
            'legal_domain_drafts' => LegalDomainResource::collection($this->whenLoaded('legalDomainDrafts')),
            'legal_domains' => LegalDomainResource::collection($this->whenLoaded('legalDomains')),
            'linked_children' => ReferenceResource::collection($this->whenLoaded('linkedChildren')),
            'linked_parents' => ReferenceResource::collection($this->whenLoaded('linkedParents')),
            'location_drafts' => LocationResource::collection($this->whenLoaded('locationDrafts')),
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
            'summary' => new SummaryResource($this->whenLoaded('summary')),
            'summary_draft' => new SummaryResource($this->whenLoaded('summaryDraft')),
            'tag_drafts' => TagResource::collection($this->whenLoaded('tagDrafts')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'parent_toc_item_label' => $this->whenLoaded('latestTocItem', fn () => $this->latestTocItem?->parent?->label ?? null),
            'parent_parent_toc_item_label' => $this->whenLoaded('latestTocItem', fn () => $this->latestTocItem?->parent?->parent?->label ?? null),
            'parent_parent_parent_toc_item_label' => $this->whenLoaded('latestTocItem', fn () => $this->latestTocItem?->parent?->parent?->parent->label ?? null),

            'work' => new WorkResource($this->whenLoaded('work')),
            'title' => $this->whenLoaded('refPlainText', fn () => $this->refPlainText->plain_text ?? ''),
            'selectors' => $this->whenLoaded('refSelector', fn () => $this->refSelector->selectors ?? []),
            'link_type' => $this->when(isset($this->pivot->link_type), $this->pivot->link_type ?? null),
        ];
    }
}
