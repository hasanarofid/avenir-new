<?php

namespace App\Events;

use App\Models\KIBrief;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KIBriefPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $kiBrief;

    /**
     * Create a new event instance.
     */
    public function __construct(KIBrief $kiBrief)
    {
        $this->kiBrief = $kiBrief;
    }
}
