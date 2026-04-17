<?php

namespace Webkul\Lead\Services\Actions;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadActionInterface;
use Webkul\Tag\Models\Tag;

/**
 * Add Tag Action
 *
 * Attaches a tag to the lead.
 */
class AddTagAction implements LeadActionInterface
{
    public function execute(array $params, $context): mixed
    {
        $tagName = $params['tag'] ?? null;

        if (! $tagName) {
            throw new \InvalidArgumentException('AddTagAction requires "tag" parameter');
        }

        if (! $context instanceof Lead) {
            throw new \InvalidArgumentException('AddTagAction requires Lead context');
        }

        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $context->tags()->syncWithoutDetaching([$tag->id]);

        return ['tag_id' => $tag->id, 'tag_name' => $tagName];
    }

    public function name(): string
    {
        return 'add_tag';
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