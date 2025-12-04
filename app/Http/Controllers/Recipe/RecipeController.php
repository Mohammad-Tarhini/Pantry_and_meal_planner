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
        try{
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("This user is not authorized", 400);
            }
            $data=$request->validate([
                'houseHolderId'=>'sometime|int'
            ]);
            $houseHolderId=$data['houseHolderId'];
            $result=$this->RecipesService->getAllRecipesForHouseHolder($user,$houseHolderId);
            return ResponseTrait::success(data:$result);
        }catch(\Exception $e){
            return ResponseTrait::error($e->getMessage(), 400);
        }
    }
    function GetIngrediantForRecipe(Request $request){
        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("This user is not authorized", 400);
            }
            $data=$request->validate([
                'recipe_id'=>'required|int'
            ]);
            $recipe_id=$data['recipe_id'];
            $result=$this->RecipeService->GetIngrediantForRecipeService($recipe_id);
            return ResponseTrait::success($result);

        }catch(\Exception $e){
            return ResponseTrait::error($e->getMessage(), 400);
        }
    }
    function delete(Request $request){
        try{
            $user=Auth::user();
            if(!$user){
                return ResponseTrait::error("the user is not authorized");
            }
            $request->validate([
                'recipe_id'=>'require|int ',
            ]);
            $recipe_id=$request->input('recipe_id');
            $result=$this->$RecipesService->deleteService($user,$recipe_id);
            return Responsetrait::success(data:$result,message:"this data is deleted correctly");



        }catch(\Exception $e){
            return ResponseTrait::error($e->getMessage(),400);
        }
    }
    function updateRecipe(Request $request){
        try{


        }catch(\Exception $e){
            return ResponseTrait::error($e->getMessage(),400);
        }
    }
     
}

   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}
