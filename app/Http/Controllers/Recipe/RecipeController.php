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

        if (!$user->household) {
            return ResponseTrait::error("This user does not have a household.", 400);
        }

        $result = RecipesService::saveRecipeService($data, $user->household->id);
        

        return ResponseTrait::success($result, "Recipe saved successfully");
        
    } catch(\Exception $e){
        return ResponseTrait::error($e->getMessage(), 400);
    }

    function CreateRecipeByAiReturnToUser(Request $request){
        try{
            $request->validate([
                'usertext'=>'required|string',

            ]);
            $userText=$request->input('userText');

            $user = Auth::user();
    
            if (!$user->household) {
                return ResponseTrait::error("This user does not have a household.", 400);
            }
    
            $result = RecipesService::createRecipseByAiASUserWantAndReturnToCLient($userText, $user->household->id);
            if($result['success']){
                return ResponseTrait::success($result['messsage'],$result['data']);
            }
            else {
                return ResponseTrait::error($result['message']);
            }

        }catch(\Exception $e){
            return ResponseTrait::error($e->getMessage(), 400);
        }
    }
}

   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}
