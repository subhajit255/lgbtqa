<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Community;
use App\Models\Chat;
use App\Models\ChatParticipant;

class BackfillCommunityChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'community:backfill-chats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates group chats for communities that do not have one and syncs participants.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $communities = Community::doesntHave('chat')->get();
        $this->info("Found {$communities->count()} communities without a chat.");

        foreach ($communities as $community) {
            $chat = Chat::create([
                'is_group' => true,
                'name' => $community->name,
                'admin_id' => $community->creator_id,
                'community_id' => $community->id,
            ]);

            $activeMembers = $community->members()->where('status', 'active')->get();
            
            foreach ($activeMembers as $member) {
                ChatParticipant::firstOrCreate([
                    'chat_id' => $chat->id,
                    'user_id' => $member->user_id,
                ], [
                    'role' => $member->role == 'creator' ? 'admin' : 'member',
                ]);
            }
            
            $this->info("Created chat for community: {$community->name} with {$activeMembers->count()} participants.");
        }

        $this->info('Backfill completed successfully!');
    }
}
