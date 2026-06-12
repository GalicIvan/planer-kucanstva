<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShoppingItemRequest;
use App\Models\ShoppingItem;
use Illuminate\Http\Request;

class ShoppingItemController extends Controller
{
    /**
     * GET /api/shopping-items
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json([]);
        }

        $query = ShoppingItem::query()
            ->where('household_id', $household->id)
            ->with('creator:id,name');

        if ($request->has('is_purchased')) {
            $query->where('is_purchased', $request->boolean('is_purchased'));
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * POST /api/shopping-items
     */
    public function store(ShoppingItemRequest $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json(['message' => 'You must belong to a household before adding items.'], 422);
        }

        $item = ShoppingItem::create([
            'household_id' => $household->id,
            'created_by_user_id' => $user->id,
            'name' => $request->name,
            'quantity' => $request->quantity ?? 1,
            'is_purchased' => $request->boolean('is_purchased'),
        ]);

        return response()->json($item->load('creator:id,name'), 201);
    }

    /**
     * PUT/PATCH /api/shopping-items/{shoppingItem}
     */
    public function update(ShoppingItemRequest $request, ShoppingItem $shoppingItem)
    {
        $shoppingItem->fill($request->only(['name', 'quantity', 'is_purchased']));
        $shoppingItem->save();

        return response()->json($shoppingItem->load('creator:id,name'));
    }

    /**
     * PATCH /api/shopping-items/{shoppingItem}/purchased
     */
    public function markPurchased(Request $request, ShoppingItem $shoppingItem)
    {
        $data = $request->validate([
            'is_purchased' => ['required', 'boolean'],
        ]);

        $shoppingItem->update($data);

        return response()->json($shoppingItem->load('creator:id,name'));
    }

    /**
     * DELETE /api/shopping-items/{shoppingItem}
     */
    public function destroy(ShoppingItem $shoppingItem)
    {
        $shoppingItem->delete();

        return response()->json(['message' => 'Item deleted.']);
    }
}
