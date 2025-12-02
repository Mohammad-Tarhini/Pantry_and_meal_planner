<?php

namespace App\Http\Controllers\pantryItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use app\Traits\ResponsTraits;
use app\Models\Householder;
use app\services\PantryItemServices;

class PantryItemController extends Controller
{
    public function getPantryItems(Request $request){
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("User is not found", 404);
            }
    
            $household = Household::where('user_id', $user->id)->first();
            if (!$household) {
                return ResponseTrait::error("Household not found for this user", 404);
            }
    
            $householdId = $household->id;
    
            $result = PantryItemServices::GetAllPantryItemsForHouseHolder($householdId);
            return ResponseTrait::success($result, "Pantry items retrieved successfully");
            
        } catch (\Exception $e) {
            return ResponseTrait::error("An error occurred", 500, $e->getMessage());
        }
    }


    
    public function AddPlantery(Request $request){
        try{
            $user=Auth::user();
            if(!$user){
                return Response::error("user is not authonticated");
            }
            $data = $request->validate([
                'name' => 'required|string',
                'quantity' => 'required|int',
                'unit' => 'required|exists:units,name',
                'expiry_date' => 'required|date',
                'location' => 'required|string',


            ]);
            $result=PantryItemServices::AddPantryItem($date);
            return ResponsTraits::success(data:$result);

        }catch(\exception $e){
            return ResponsTraits::error(error:$e);
        }
    }
     
}
