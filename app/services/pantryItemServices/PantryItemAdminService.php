<?php


namespace App\Services;

use App\Models\PantryItem;
use App\Models\User;
use App\Models\Household;
use Illuminate\Http\Request;
use Exception;

class AdminPantryService
{
    /**
     * Return pantry items for admin. If no filters are provided returns all items.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getPantryItemsAdmin(Request $request)
    {
        $request->validate([
            'householder_id' => 'sometimes|integer|exists:users,id',
            'email'          => 'sometimes|email|exists:users,email',
        ]);

        // No filters -> return all
        if (!$request->filled('householder_id') && !$request->filled('email')) {
            return PantryItem::all();
        }

        // Resolve user by id or email
        $user = $request->filled('householder_id')
            ? User::find($request->householder_id)
            : User::where('email', $request->email)->first();

        if (!$user) {
            throw new Exception("User not found");
        }

        $household = Household::where('user_id', $user->id)->first();

        if (!$household) {
            throw new Exception("Household not found for the given user");
        }

        return PantryItem::where('household_id', $household->id)->get();
    }
}

// class AdminPantryService
// {
//     public function getPantryItemsAdmin($request)
//     {
//         $request->validate([
//             'householder_id' => 'sometimes|integer|exists:users,id',
//             'email'          => 'sometimes|email|exists:users,email',
//         ]);

//         // Admin wants all data
//         if (!$request->householder_id && !$request->email) {
//             return PantryItem::all();
//         }

//         // Find the user
//         $user = $request->householder_id
//             ? User::find($request->householder_id)
//             : User::where('email', $request->email)->first();

//         $household = HouseHold::where('user_id', $user->id)->first();

//         if (!$household) {
//             throw new \Exception("Household not found", 404);
//         }

//         return PantryItem::where('household_id', $household->id)->get();
//     }
// }


?>
