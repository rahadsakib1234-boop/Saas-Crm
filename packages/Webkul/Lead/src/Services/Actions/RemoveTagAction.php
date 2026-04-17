<?php

namespace Webkul\Lead\Services\Actions;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadActionInterface;
use Webkul\Tag\Models\Tag;

/**
 * Remove Tag Action
 *
 * Removes a tag from the lead.
 */
class RemoveTagAction implements LeadActionInterface
{
    public function execute(array $params, $context): mixed
    {
        $tagName = $params['tag'] ?? null;

        if (! $tagName) {
            throw new \InvalidArgumentException('RemoveTagAction requires "tag" parameter');
        }

        if (! $context instanceof Lead) {
            throw new \InvalidArgumentException('RemoveTagAction requires Lead context');
        }

        $tag = Tag::where('name', $tagName)->first();

        if (! $tag) {
            return ['removed' => false, 'reason' => 'tag_not_found'];
        }

        $context->tags()->detach($tag->id);

        return ['tag_id' => $tag->id, 'tag_name' => $tagName, 'removed' => true];
    }

    public function name(): string
    {
        return 'remove_tag';
    }

    public function validate(array $params): bool
    {
        return isset($params['tag']) && is_string($params['tag']);
    }

    public function requiredParams(): array
    {
        return ['tag'];
    }
}