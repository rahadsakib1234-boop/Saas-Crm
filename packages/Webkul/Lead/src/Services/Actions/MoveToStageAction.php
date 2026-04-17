<?php

namespace Webkul\Lead\Services\Actions;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadActionInterface;

/**
 * Move to Stage Action
 *
 * Changes the lead's pipeline stage.
 */
class MoveToStageAction implements LeadActionInterface
{
    public function execute(array $params, $context): mixed
    {
        if (! $context instanceof Lead) {
            throw new \InvalidArgumentException('MoveToStageAction requires Lead context');
        }

        $stageId = $params['stage_id'] ?? null;
        $stageCode = $params['stage_code'] ?? null;

        if (! $stageId && ! $stageCode) {
            throw new \InvalidArgumentException('MoveToStageAction requires "stage_id" or "stage_code"');
        }

        if ($stageCode) {
            $stage = \Webkul\Lead\Models\Stage::where('code', $stageCode)->first();
            if (! $stage) {
                throw new \InvalidArgumentException("Stage not found: {$stageCode}");
            }
            $stageId = $stage->id;
        }

        $oldStageId = $context->lead_pipeline_stage_id;
        $context->lead_pipeline_stage_id = $stageId;
        $context->save();

        return [
            'old_stage_id' => $oldStageId,
            'new_stage_id' => $stageId,
            'lead_id' => $context->id,
        ];
    }

    public function name(): string
    {
        return 'move_to_stage';
    }

    public function validate(array $params): bool
    {
        return isset($params['stage_id']) || isset($params['stage_code']);
    }

    public function requiredParams(): array
    {
        return ['stage_id']; // stage_code also valid
    }
}