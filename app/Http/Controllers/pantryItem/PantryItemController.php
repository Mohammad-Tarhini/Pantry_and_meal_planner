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
    public function getPantryItems(Request $request)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser) {
                return ResponseTrait::error("User not found", 404);
            }
            $result=$this->PantryItemService->getPantryItem($request);
            return ResponseTrait::success(data:$result);
        } catch (\Exception $e) {
            return ResponseTrait::error("An error occurred", 500, $e->getMessage());
        }
    }

    public function AddPlanteryItem(Request $request){
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
            $result=PantryItemServices::AddPantryItemService($date,$user);
            return ResponsTraits::success(data:$result);

        }catch(\exception $e){
            return ResponsTraits::error(error:$e);
        }
    }
    public function UpdatePantryItem(Request $request){
       try{
            $user=Auth::user();
            if(!$user){
                   return Response::error("user is not authonticated");
               }
               $data = $request->validate([
                   'name' => 'sometime|string',
                   'quantity' => 'sometime|int',
                   'unit' => 'sometime|exists:units,name',
                   'location' => 'sometime|string',
               ]);
            $result=PantryItemServices::UpdatePantryItemService($date,$user);
            return ResponsTraits::success(data:$result);
       }catch(\exception $e){
         return ResponsTraits::error(error:$e);
       }
    }
    public function GetPantryItemById(Request $request){
        try{
            $user=Autho::user();
            if(!$user){
                       return Response::error("user is not authonticated");
            }
            $data= $request->validate([
                'item_id'=>'require|int',
            ]);
    
            $result=PantryItemServices->GetPantryItemByIdService($data,$user);
        }catch(\exception $e){
            return ResponsTraits::error(error:$e);
        }
        
    }
}
