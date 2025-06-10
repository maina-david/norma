<?php

namespace App\Enums\Auth;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static UserActivityType viewedTag()
 * @method static UserActivityType normaComment()
 * @method static UserActivityType normaActivate()
 * @method static UserActivityType referenceComment()
 * @method static UserActivityType referenceCommentReply()
 * @method static UserActivityType normaCommentReply()
 * @method static UserActivityType updateNotificationPreferences()
 * @method static UserActivityType legalUpdateRead()
 * @method static UserActivityType legalUpdateUnderstood()
 * @method static UserActivityType normaCheckIn()
 * @method static UserActivityType viewedReference()
 * @method static UserActivityType viewedTabSummary()
 * @method static UserActivityType viewedTabSection()
 * @method static UserActivityType viewedTabConsequences()
 * @method static UserActivityType viewedTabReminders()
 * @method static UserActivityType viewedTabComments()
 * @method static UserActivityType viewedTabFiles()
 * @method static UserActivityType viewedFullText()
 * @method static UserActivityType failedSearch()
 * @method static UserActivityType searchTerm()
 * @method static UserActivityType viewedRequirements()
 * @method static UserActivityType downloadedDocument()
 * @method static UserActivityType accessedPlatform()
 * @method static UserActivityType viewedTabReadSummary()
 * @method static UserActivityType legalUpdateComment()
 * @method static UserActivityType legalUpdateCommentReply()
 * @method static UserActivityType documentComment()
 * @method static UserActivityType documentCommentReply()
 * @method static UserActivityType assessmentItemResponseComment()
 * @method static UserActivityType assessmentItemResponseCommentReply()
 * @method static UserActivityType legalReportFilter()
 * @method static UserActivityType knowledgebaseSearch()
 * @method static UserActivityType referenceReminder()
 * @method static UserActivityType documentReminder()
 * @method static UserActivityType assessmentItemReminder()
 * @method static UserActivityType taskReminder()
 * @method static UserActivityType assessmentItemResponseTask()
 * @method static UserActivityType documentTask()
 * @method static UserActivityType normaTask()
 * @method static UserActivityType referenceTask()
 * @method static UserActivityType fulltextSearchTerm()
 * @method static UserActivityType updatesTask()
 * @method static UserActivityType taskComment()
 * @method static UserActivityType taskCommentReply()
 * @method static UserActivityType translatedContent()
 * @method static UserActivityType bulkAnsweredAssessmentItems()
 * @method static UserActivityType createdAssessmentItem()
 * @method static UserActivityType exportedAssessmentItems()
 * @method static UserActivityType filteredAssessmentItems()
 * @method static UserActivityType searchedAssessmentItems()
 * @method static UserActivityType bookmarkedLegalUpdate()
 * @method static UserActivityType exportedLegalUpdate()
 * @method static UserActivityType filteredLegalUpdate()
 * @method static UserActivityType searchedLegalUpdate()
 * @method static UserActivityType createdProject()
 * @method static UserActivityType previewedFile()
 * @method static UserActivityType searchedFiles()
 * @method static UserActivityType uploadedDocument()
 * @method static UserActivityType viewedFolder()
 * @method static UserActivityType userActivated()
 * @method static UserActivityType viewedCalendar()
 * @method static UserActivityType changedCalendarDates()
 * @method static UserActivityType bookmarkedLegislation()
 * @method static UserActivityType filteredPerformanceReport()
 * @method static UserActivityType exportedConsolidatedPerformanceReport()
 * @method static UserActivityType exportedLegalRegister()
 * @method static UserActivityType printedRequirementDocument()
 * @method static UserActivityType searchedTasks()
 * @method static UserActivityType filteredTasks()
 * @method static UserActivityType filteredApplicability()
 * @method static UserActivityType bulkAnsweredApplicability()
 * @method static UserActivityType answeredApplicability()
 * @method static UserActivityType viewedApplicabilityQuestion()
 * @method static UserActivityType createdApplicabilityTask()
 * @method static UserActivityType modifiedApplicabilityRequirement()
 * @method static UserActivityType downloadedApplicabilityTemplate()
 * @method static UserActivityType uploadedApplicabilityTemplate()
 * @method static UserActivityType viewedUpdateDocument()
 * @method static UserActivityType viewedRequirementReadWithTab()
 * @method static UserActivityType viewedRequirementAmendmentsTab()
 * @method static UserActivityType editedFileInformation()
 * @method static UserActivityType commentedOnApplicability()
 * @method static UserActivityType viewedApplicabilityRequirements()
 * @method static UserActivityType activatedMultiStream()
 */
class UserActivityType extends Enum
{
    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'viewedTag' => 1,
            'normaComment' => 2,
            'normaActivate' => 3,
            'referenceComment' => 4,
            'referenceCommentReply' => 5,
            'normaCommentReply' => 6,
            'updateNotificationPreferences' => 7,
            'legalUpdateRead' => 8,
            'legalUpdateUnderstood' => 9,
            'normaCheckIn' => 10,
            'viewedReference' => 11,
            'viewedTabSummary' => 12,
            'viewedTabSection' => 13,
            'viewedTabConsequences' => 14,
            'viewedTabReminders' => 15,
            'viewedTabComments' => 16,
            'viewedTabFiles' => 17,
            'viewedFullText' => 18,
            'failedSearch' => 19,
            'searchTerm' => 20,
            'viewedRequirements' => 21,
            'downloadedDocument' => 22,
            'accessedPlatform' => 23,
            'viewedTabReadSummary' => 24,
            'legalUpdateComment' => 25,
            'legalUpdateCommentReply' => 26,
            'documentComment' => 27,
            'documentCommentReply' => 28,
            'assessmentItemResponseComment' => 29,
            'assessmentItemResponseCommentReply' => 30,
            // 'legalReportFilter' => 31,
            'knowledgebaseSearch' => 32,
            'referenceReminder' => 33,
            'documentReminder' => 34,
            'assessmentItemReminder' => 35,
            'taskReminder' => 36,
            'assessmentItemResponseTask' => 37,
            'documentTask' => 38,
            'normaTask' => 39,
            'referenceTask' => 40,
            'fulltextSearchTerm' => 41,
            'updatesTask' => 42,
            'taskComment' => 43,
            'taskCommentReply' => 44,
            'translatedContent' => 45,

            'bulkAnsweredAssessmentItems' => 46,
            'createdAssessmentItem' => 47,
            'exportedAssessmentItems' => 48,
            'filteredAssessmentItems' => 49,
            'searchedAssessmentItems' => 50,
            'bookmarkedLegalUpdate' => 51,
            'exportedLegalUpdate' => 52,
            'filteredLegalUpdate' => 53,
            'searchedLegalUpdate' => 54,
            'createdProject' => 55,
            'previewedFile' => 56,
            'searchedFiles' => 57,
            'uploadedDocument' => 58,
            'viewedFolder' => 59,
            'userActivated' => 60,
            'viewedCalendar' => 61,
            'changedCalendarDates' => 62,
            'bookmarkedLegislation' => 63,
            'filteredPerformanceReport' => 64,
            'exportedConsolidatedPerformanceReport' => 65,
            'exportedLegalRegister' => 66,
            'printedRequirementDocument' => 67,
            'searchedTasks' => 68,
            'filteredTasks' => 69,
            'filteredApplicability' => 70,
            'bulkAnsweredApplicability' => 71,
            'answeredApplicability' => 72,
            'viewedApplicabilityQuestion' => 73,
            'createdApplicabilityTask' => 74,
            'modifiedApplicabilityRequirement' => 75,
            'downloadedApplicabilityTemplate' => 76,
            'uploadedApplicabilityTemplate' => 77,
            'viewedUpdateDocument' => 78,
            'viewedRequirementReadWithTab' => 79,
            'viewedRequirementAmendmentsTab' => 80,
            'editedFileInformation' => 81,
            'commentedOnApplicability' => 82,
            'viewedApplicabilityRequirements' => 83,
            'activatedMultiStream' => 84,
        ];
    }
}
