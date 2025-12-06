<?php

namespace App\Http\Controllers\PantryItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseTrait;
use App\Services\PantryItemService;

class PantryItemController extends Controller
{
    use ResponseTrait;

    protected $pantryItemService;

    public function __construct(PantryItemService $pantryItemService)
    {
        $this->pantryItemService = $pantryItemService;
    }

    public function getPantryItems(Request $request)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser) {
                return $this->error("User not authenticated", 401);
            }

            $result = $this->pantryItemService->getPantryItems($request, $authUser);
            return $this->success(data: $result);
        } catch (\Exception $e) {
            return $this->error("An error occurred: " . $e->getMessage(), 500);
        }
    }

    public function addPantryItem(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error("User not authenticated", 401);
            }

            $data = $request->validate([
                'name' => 'required|string',
                'quantity' => 'required|integer',
                'unit' => 'required|exists:units,name',
                'expiry_date' => 'nullable|date',
                'location' => 'nullable|string',
            ]);

            $result = $this->pantryItemService->addPantryItem($data, $user);

            return $this->success(data: $result, message: 'Pantry item added');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function updatePantryItem(Request $request, $itemId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error("User not authenticated", 401);
            }

            $data = $request->validate([
                'name' => 'sometimes|string',
                'quantity' => 'sometimes|integer',
                'unit' => 'sometimes|exists:units,name',
                'expiry_date' => 'sometimes|date',
                'location' => 'sometimes|string',
            ]);

            $result = $this->pantryItemService->updatePantryItem($itemId, $data, $user);

            return $this->success(data: $result, message: 'Pantry item updated');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getPantryItemById(Request $request, $itemId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error("User not authenticated", 401);
            }

            $result = $this->pantryItemService->getPantryItemById($itemId, $user);

            return $this->success(data: $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function deletePantryItem(Request $request, $itemId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error("User not authenticated", 401);
            }

            $this->pantryItemService->deletePantryItem($itemId, $user);

            return $this->success(null, 'Pantry item deleted');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}


// namespace App\Http\Controllers\pantryItem;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use app\Traits\ResponsTraits;
// use app\Models\Householder;
// use app\services\PantryItemServices;

// class PantryItemController extends Controller
// {
//     public function getPantryItems(Request $request)
//     {
//         try {
//             $authUser = Auth::user();
//             if (!$authUser) {
//                 return ResponseTrait::error("User not found", 404);
//             }
//             $result=$this->PantryItemService->getPantryItem($request);
//             return ResponseTrait::success(data:$result);
//         } catch (\Exception $e) {
//             return ResponseTrait::error("An error occurred", 500, $e->getMessage());
//         }
//     }

//     public function AddPlanteryItem(Request $request){
//         try{
//             $user=Auth::user();
//             if(!$user){
//                 return Response::error("user is not authonticated");
//             }
//             $data = $request->validate([
//                 'name' => 'required|string',
//                 'quantity' => 'required|int',
//                 'unit' => 'required|exists:units,name',
//                 'expiry_date' => 'required|date',
//                 'location' => 'required|string',


//             ]);
//             $result=PantryItemServices::AddPantryItemService($date,$user);
//             return ResponsTraits::success(data:$result);

//         }catch(\exception $e){
//             return ResponsTraits::error(error:$e);
//         }
//     }
//     public function UpdatePantryItem(Request $request){
//        try{
//             $user=Auth::user();
//             if(!$user){
//                    return Response::error("user is not authonticated");
//                }
//                $data = $request->validate([
//                    'name' => 'sometime|string',
//                    'quantity' => 'sometime|int',
//                    'unit' => 'sometime|exists:units,name',
//                    'location' => 'sometime|string',
//                ]);
//             $result=$this->PantryItemServices->UpdatePantryItemService($date,$user);
//             return ResponsTraits::success(data:$result);
//        }catch(\exception $e){
//          return ResponsTraits::error(error:$e);
//        }
//     }
//     public function GetPantryItemById(Request $request){
//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return Response::error("user is not authonticated");
//             }
//             $data= $request->validate([
//                 'item_id'=>'require|int',
//             ]);
//             $item_id=$data['item_id'];
//             $result=$this->PantryItemServices->GetPantryItemByIdService($item_id,$user);
//             return ResponseTraits::success(data:$result);
//         }catch(\exception $e){
//             return ResponsTraits::error(error:$e);
//         }
//     }
//     public function DeletePantryItemByID(Request $request){
//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return Response::error("user is not authonticated");
//             }
//             $data= $request->validate([
//                 'item_id'=>'require|int',
                
//             ]);
//             $item_id=$data['item_id'];
//             $result=$this->PantryItemServices->DeletePantryItemService($item_id,$user);
//             return ResponsTraits::success(data:$result,message:"deleted");

//         }catch(\exception $e){
//             return ResponsTraits::error(error:$e);
//         }
//     }

    
// }
