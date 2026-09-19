<?php

namespace App\Console\Commands;

use App\Models\BlockedName;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('blocked-names:list')]
#[Description('Show every blocked name.')]
class BlockedNameList extends Command
{
    public function handle(): int
    {
        $rows = BlockedName::orderBy('term')->get(['term', 'match', 'reason', 'created_at']);

        if ($rows->isEmpty()) {
            $this->warn('The blocklist is empty.');

            return self::SUCCESS;
        }

        $this->table(['Term', 'Match', 'Reason', 'Added'], $rows->map(fn (BlockedName $row) => [
            $row->term, $row->match, $row->reason ?? '', $row->created_at?->toDateString() ?? '',
        ])->all());

        return self::SUCCESS;
    }
}
