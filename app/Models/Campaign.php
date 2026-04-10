<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['objective', 'status', 'analysis', 'strategy', 'creative', 'audit', 'quality_score'])]
class Campaign extends Model
{
    protected function casts(): array
    {
        return [
            'analysis' => 'array',
            'strategy' => 'array',
            'creative' => 'array',
            'audit' => 'array',
            'quality_score' => 'integer',
        ];
    }

    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
