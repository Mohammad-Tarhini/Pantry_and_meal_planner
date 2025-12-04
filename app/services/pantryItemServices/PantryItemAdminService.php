<?php
class AdminPantryService
{
    public function getPantryItemsAdmin($request)
    {
        $request->validate([
            'householder_id' => 'sometimes|integer|exists:users,id',
            'email'          => 'sometimes|email|exists:users,email',
        ]);

        // Admin wants all data
        if (!$request->householder_id && !$request->email) {
            return PantryItem::all();
        }

        // Find the user
        $user = $request->householder_id
            ? User::find($request->householder_id)
            : User::where('email', $request->email)->first();

        $household = HouseHold::where('user_id', $user->id)->first();

        if (!$household) {
            throw new \Exception("Household not found", 404);
        }

        return PantryItem::where('household_id', $household->id)->get();
    }
}


?>
