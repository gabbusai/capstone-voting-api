<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\VoteStatus;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Position;
use App\Models\VoteLogs;
use App\Models\Election;
use App\Models\User;
use App\Models\VoteLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    //trial voting code
    public function castVote(Request $request)
{
    $request->validate([
        'election_id' => 'required|integer|exists:elections,id',
        //'user_id' => 'required|integer|exists:users,id',
        'votes' => 'required|array|min:1',
        'votes.*.position_id' => 'required|integer|exists:positions,id',
        'votes.*.candidate_id' => 'required|integer|exists:candidates,id',
    ]);

    $election = Election::findOrFail($request->election_id);
    $currentDate = Carbon::now();

    // ✅ Check if election is ongoing
    if ($currentDate->lt($election->election_start_date) || $currentDate->gt($election->election_end_date)) {
        return response()->json(['message' => 'Voting is closed.'], 403);
    }

    // ✅ Check if voter exists
    $user = User::find(Auth::user()->id);
    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // ✅ Check if user has already voted in this election
    $voteStatus = VoteStatus::where('user_id', $user->id)
                            ->where('election_id', $request->election_id)
                            ->first();

    if ($voteStatus && $voteStatus->has_voted) {
        return response()->json(['message' => 'You have already voted in this election.'], 403);
    }

    DB::beginTransaction();
    try {
        foreach ($request->votes as $vote) {
            $positionId = $vote['position_id'];
            $candidateId = $vote['candidate_id'];

            // ✅ Prevent duplicate votes for the same position
            $existingVote = Vote::where('user_id', $user->id)
                                ->where('position_id', $positionId)
                                ->where('election_id', $request->election_id)
                                ->exists();

            if ($existingVote) {
                return response()->json([
                    'message' => 'You have already voted for this position.'
                ], 403);
            }

            // ✅ Save vote record
            Vote::create([
                'user_id' => $user->id,
                'voter_student_id' => $user->student_id,
                'position_id' => $positionId,
                'position_name' => Position::find($positionId)->name,
                'candidate_id' => $candidateId,
                'candidate_student_id' => Candidate::find($candidateId)->student_id,
                'candidate_name' => Candidate::find($candidateId)->user->name,
                'election_id' => $request->election_id,
            ]);
        }

        // ✅ Update `vote_statuses` to mark voter as voted
        if ($voteStatus) {
            $voteStatus->update([
                'has_voted' => true,
                'voted_at' => $currentDate,
            ]);
        } else {
            VoteStatus::create([
                'user_id' => $user->id,
                'election_id' => $request->election_id,
                'voted_at' => $currentDate,
                'has_voted' => true,
            ]);
        }

        DB::commit();
        return response()->json(['message' => 'Vote successfully cast.'], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Vote failed.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function getElectionDetails($electionId)
{
    $user = Auth::user();

    // Fetch election with candidates and their positions
    $election = Election::with(['candidates.position'])->find($electionId);
    $department = Department::find($election->department_id);

    if (!$election) {
        return response()->json(['message' => 'Election not found'], 404);
    }

    // Check if the user has voted in this election
    $userVotes = Vote::where('user_id', $user->id)->where('election_id', $electionId)->get();
    $hasVoted = $userVotes->isNotEmpty();

    // Map user votes to positions
    $userVoteDetails = [];
    if ($hasVoted) {
        foreach ($userVotes as $vote) {
            $candidate = Candidate::with('position')->find($vote->candidate_id);
            $userVoteDetails[] = [
                'position_id' => $vote->position_id,
                'position_name' => $candidate ? $candidate->position->name : 'Unknown',
                'candidate_id' => $vote->candidate_id,
                'candidate_name' => $candidate ? $candidate->student->name : 'Abstained',
            ];
        }
    }

    // Group candidates by position
    $positions = [];
    foreach ($election->candidates as $candidate) {
        $positionId = $candidate->position_id;
        if (!isset($positions[$positionId])) {
            $positions[$positionId] = [
                'id' => $candidate->position->id,
                'name' => $candidate->position->name,
                'candidates' => []
            ];
        }
        $positions[$positionId]['candidates'][] = [
            'id' => $candidate->id,
            'name' => $candidate->user->name,
            'profile_photo' => $candidate->profile_photo,
            'party_list_id' => $candidate->party_list_id,
        ];
    }

    return response()->json([
        'election' => [
            'id' => $election->id,
            'election_type_id' => $election->election_type_id,
            'department_id' => $election->department_id,
            'department' => $department->name,
            'name' => $election->election_name,
            'campaign_start_date' => $election->campaign_start_date,
            'campaign_end_date' => $election->campaign_end_date,
            'election_start_date' => $election->election_start_date,
            'election_end_date' => $election->election_end_date,
            'status' => $election->status,
            'positions' => array_values($positions), // Convert associative array to indexed array
        ],
        'hasVoted' => $hasVoted,
        'userVoteDetails' => $hasVoted ? $userVoteDetails : null,
    ]);
}

    //get election results
    public function getElectionResults($electionId)
    {
        // Fetch the election details
        $election = Election::with(['candidates.position', 'candidates.partylist'])->find($electionId);

        if (!$election) {
            return response()->json(['message' => 'Election not found'], 404);
        }

        // Fetch all candidates and count votes per candidate
        $tallies = Vote::where('election_id', $electionId)
            ->selectRaw('candidate_id, COUNT(*) as vote_count')
            ->groupBy('candidate_id')
            ->get()
            ->keyBy('candidate_id'); // Key the results by candidate_id for easy lookup

        // Organize results by position
        $results = [];
        foreach ($election->candidates as $candidate) {
            $positionId = $candidate->position->id;
            $positionName = $candidate->position->name;

            if (!isset($results[$positionId])) {
                $results[$positionId] = [
                    'position_id' => $positionId,
                    'position_name' => $positionName,
                    'candidates' => [],
                    'winners' => [] // Adjusted to allow multiple winners
                ];
            }

            $voteCount = $tallies[$candidate->id]->vote_count ?? 0;

            $results[$positionId]['candidates'][] = [
                'candidate_id' => $candidate->id,
                'name' => $candidate->user->name ?? 'Unknown',
                'profile_photo' => $candidate->profile_photo ?? null,
                'partylist' => $candidate->partylist->name ?? 'Independent',
                'votes' => $voteCount
            ];
        }

        // Determine winners (including ties and no-vote handling)
        foreach ($results as &$position) {
            if (empty($position['candidates'])) {
                $position['winners'] = ['No candidates for this position'];
                continue;
            }

            // Sort candidates by votes in descending order
            $position['candidates'] = collect($position['candidates'])->sortByDesc('votes')->values();

            // Check if votes exist
            if ($position['candidates'][0]['votes'] === 0) {
                $position['winners'] = ['No votes received for this position'];
            } else {
                $highestVote = $position['candidates'][0]['votes'];
                $position['winners'] = $position['candidates']->filter(function ($candidate) use ($highestVote) {
                    return $candidate['votes'] === $highestVote;
                })->values();
            }
        }

        return response()->json([
            'election' => [
                'id' => $election->id,
                'name' => $election->election_name,
                'status' => $election->status
            ],
            'results' => array_values($results) // Ensure clean JSON response
        ]);
    }


    //end of block
}








    /*// Function to start the voting process
    public function startVoting($electionId)
    {
        $userId = Auth::user()->id;

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
        
        $validatedData = $request->validated();
        $electionId = $validatedData['election_id'];
        $votes = $validatedData['votes'];
        $userId = Auth::user()->id;

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
        $userId = Auth::user()->id;

        // Fetch votes based on the vote status ID
        $votes = Vote::where('user_id', $userId)
            ->where('election_id', $electionId)
            ->with(['candidate', 'position'])
            ->get();

        return response()->json(['votes' => $votes]);
    }*/
