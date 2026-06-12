<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HouseholdController extends Controller
{
    /**
     * GET /api/household
     * Returns the current user's household with its members.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $household = $user->households()->with('members')->first();

        if (!$household) {
            return response()->json(['message' => 'You are not part of a household yet.'], 404);
        }

        return response()->json($household);
    }

    /**
     * POST /api/household
     * Creates a household and makes the creator its admin member.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $household = Household::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => $user->id,
        ]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'member_role' => 'admin',
            'joined_at' => now(),
        ]);

        return response()->json($household->load('members'), 201);
    }

    /**
     * PUT /api/household/{household}
     */
    public function update(Request $request, Household $household)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $household->update($data);

        return response()->json($household->load('members'));
    }

    /**
     * GET /api/household/{household}/members
     */
    public function members(Household $household)
    {
        return response()->json($household->members);
    }

    /**
     * POST /api/household/{household}/members
     * Adds an existing user (by email) as a member of the household.
     * Restricted to admins/super_admins via route middleware.
     */
    public function addMember(Request $request, Household $household)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'member_role' => ['nullable', 'in:member,admin'],
        ]);

        $user = User::where('email', $data['email'])->first();

        $exists = HouseholdMember::where('household_id', $household->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'User is already a member of this household.'], 422);
        }

        HouseholdMember::create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'member_role' => $data['member_role'] ?? 'member',
            'joined_at' => now(),
        ]);

        return response()->json($household->load('members'), 201);
    }

    /**
     * DELETE /api/household/{household}/members/{user}
     * Restricted to admins/super_admins via route middleware.
     */
    public function removeMember(Household $household, User $user)
    {
        HouseholdMember::where('household_id', $household->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Member removed.']);
    }
}
