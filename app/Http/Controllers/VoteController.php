<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\VoteStatus;
use App\Models\Candidate;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    // Function to start the voting process
    public function startVoting($electionId)
    {
        $userId = Auth::id();

        // Check if the user already has a vote status for this election
        $voteStatus = VoteStatus::firstOrCreate(
            ['user_id' => $userId, 'election_id' => $electionId],
            ['start_time' => now(), 'has_voted' => false]
        );

        return response()->json(['message' => 'Voting session started', 'vote_status_id' => $voteStatus->id]);
    }

    // Function to cast votes for multiple positions
    public function castVotes(Request $request)
    {
        $userId = Auth::id();
        $electionId = $request->input('election_id');
        $votes = $request->input('votes'); // Array of votes [{position_id, candidate_id}, ...]

        // Validate input
        if (!$votes || !is_array($votes)) {
            return response()->json(['message' => 'Invalid vote data'], 400);
        }

        // Fetch the vote status for this election
        $voteStatus = VoteStatus::where('user_id', $userId)
            ->where('election_id', $electionId)
            ->where('has_voted', false)
            ->first();

        if (!$voteStatus) {
            return response()->json(['message' => 'No active voting session found'], 404);
        }

        DB::beginTransaction();
        try {
            // Loop through each vote entry and insert into the votes table
            foreach ($votes as $vote) {
                Vote::create([
                    'user_id' => $userId,
                    'position_id' => $vote['position_id'],
                    'candidate_id' => $vote['candidate_id'],
                    'election_id' => $electionId,
                    'vote_status_id' => $voteStatus->id,
                ]);
            }

            // Mark vote status as complete
            $voteStatus->update(['has_voted' => true, 'voted_at' => now()]);

            DB::commit();
            return response()->json(['message' => 'Votes cast successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to cast votes', 'error' => $e->getMessage()], 500);
        }
    }

    // Function to fetch all votes for a user in a specific election
    public function getUserVotes($electionId)
    {
        $userId = Auth::id();

        // Fetch votes based on the vote status ID
        $votes = Vote::where('user_id', $userId)
            ->where('election_id', $electionId)
            ->with(['candidate', 'position'])
            ->get();

        return response()->json(['votes' => $votes]);
    }
}
