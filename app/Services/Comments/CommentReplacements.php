<?php

namespace App\Services\Comments;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;
use IteratorAggregate;

class CommentReplacements
{
    /**
     * ̊̊ Replaces @mentions, emojis and preview in the comment content.
     *
     * @param IteratorAggregate<Comment> $comments
     *
     * @return IteratorAggregate<Comment>
     */
    public function replaceContent(IteratorAggregate $comments): IteratorAggregate
    {
        $comments = $this->replaceLineBreaks($comments);

        $comments = $this->replaceMentions($comments);

        $comments = $this->replaceEmojis($comments);

        return $comments;
    }

    /**
     * @param IteratorAggregate<Comment> $comments
     *
     * @return IteratorAggregate<Comment>
     */
    public function replaceMentions(IteratorAggregate $comments): IteratorAggregate
    {
        $userMentions = [];
        $mentionRegex = '/\*\*([^*]+)\*\*/';
        foreach ($comments as $comment) {
            $userMentions = [...$userMentions, ...$this->getMentions($comment->comment)];
        }
        $userMentions = array_unique($userMentions);

        /** @var Collection<int,User> */
        $users = User::whereKey($userMentions)->get(['id', 'fname', 'sname'])->keyBy('id');

        /** @var string */
        $unknownUser = __('interface.unknown');
        foreach ($comments as &$comment) {
            foreach ($this->getMentions($comment->comment) as $userId) {
                $mentionRegex = '/\*\*(user_' . $userId . ')\*\*/';
                $name = $users[$userId]->full_name ?? $unknownUser;
                $comment->comment = Str::of($comment->comment)->replaceMatches($mentionRegex, '<span class="text-primary user-mention">@' . $name . '</span>');
            }
        }

        return $comments;
    }

    /**
     * @param IteratorAggregate<Comment> $comments
     *
     * @return IteratorAggregate<Comment>
     */
    public function replaceEmojis(IteratorAggregate $comments): IteratorAggregate
    {
        $regex = '/~\|(.*)\|~/';
        foreach ($comments as &$comment) {
            $comment->comment = Str::of($comment->comment)->replaceMatches($regex, '<span class="text-primary emoji-inline">$1</span>');
        }

        return $comments;
    }

    /**
     * @param IteratorAggregate<Comment> $comments
     *
     * @return IteratorAggregate<Comment>
     */
    public function replaceLineBreaks(IteratorAggregate $comments): IteratorAggregate
    {
        foreach ($comments as &$comment) {
            $comment->comment = Str::of($comment->comment)->replaceMatches('(\r\n|\r|\n)', '<br/>');
        }

        return $comments;
    }

    /**
     * @param string $content
     *
     * @return SupportCollection<string>
     */
    private function getMentions(string $content): SupportCollection
    {
        $mentionRegex = '/\*\*([^*]+)\*\*/';

        /** @var SupportCollection<string> */
        return Str::of($content)->matchAll($mentionRegex)->map(fn ($s) => (int) str_replace('user_', '', $s));
    }
}
