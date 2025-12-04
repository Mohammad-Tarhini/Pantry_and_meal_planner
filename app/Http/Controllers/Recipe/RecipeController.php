<?php

namespace App\Http\Controllers\Recipe;

use App\Http\Controllers\Controller;
use App\services\RecipesService;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class RecipeController extends Controller
{
    //

   function saveRecipe(Request $request){
    try{
        $data = $request->validate([
            'title' => 'required|string',
            'ingredients' => 'required|array',
            'instructions' => 'required|string',
            'household_id' => 'required|integer'
        ]);

        $user = Auth::user();

        if (!$user) {
            return ResponseTrait::error("This user does not have a household.", 400);
        }

        $result =$this-> RecipesService->saveRecipeServiceService($data, $user);
        

        return ResponseTrait::success($result, "Recipe saved successfully");
        
    } catch(\Exception $e){
        return ResponseTrait::error($e->getMessage(), 400);
    }

    function CreateRecipeByAiReturnToUser(Request $request){
        try{
            $user = Auth::user();
    
            if (!$user) {
                return ResponseTrait::error("This user does not have a household.", 400);
            }           
            $request->validate([
                'usertext'=>'required|string',

            ]);
            $userText=$request->input('userText');


    
            $result = $this->RecipesService->createRecipseByAiASUserWantAndReturnToCLientService($userText, $user);
            if($result['success']){
                return ResponseTrait::success(message:$result['messsage'],data:$result['data']);
            }
            else {
                return ResponseTrait::error($result['message']);
            }

        }catch(\Exception $e){
            return ResponseTrait::error($e->getMessage(), 400);
        }
    }
    function GetAllRecipes(Request $request){
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("This user is not authorized", 400);
            }
            $data=$request->validate([
                'houseHolderId'=>'sometime|int'
            ]);
            $houseHolderId=$data['houseHolderId'];

    }
     
}

   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}
