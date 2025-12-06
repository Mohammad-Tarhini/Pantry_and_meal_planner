<?php


namespace App\Services;

use App\Models\Recipe;
use App\Models\Admin;
use App\Models\Householder;
use App\Models\HouseHolderMember;
use App\Models\RecipeIngredient;
use Illuminate\Support\Facades\DB;
use App\Services\PantryItemServices;
use App\Services\UseAIService;

class RecipesService
{
    public function getAllRecipesForHouseHolder($user, $houseHolderIdFromAdmin = null)
    {
        $admin = Admin::where('user_id', $user->id)->first();
        $member = HouseHolderMember::where('user_id', $user->id)->first();
        $householder = Householder::where('user_id', $user->id)->first();

        if ($admin) {
            return $houseHolderIdFromAdmin 
                ? Recipe::where('household_id', $houseHolderIdFromAdmin)->get()
                : Recipe::all();
        }

        if ($member) {
            $houseHolderId = $member->householder_id;
        } else {
            $houseHolderId = $householder->id;
        }

        return Recipe::where('household_id', $houseHolderId)->get();
    }

    public function createRecipeByAiAsUserWants($userTxt, $user)
    {
        $householder = Householder::where('user_id', $user->id)->first();

        if (!$householder) {
            return [
                'success' => false,
                'message' => 'User is not a householder.',
                'data' => null
            ];
        }

        $household_id = $householder->id;

        $pantryItems = PantryItemServices::GetAllPantryItemsForHouseHolder($household_id);

        $useAIService = new UseAIService();
        $response = $useAIService->getRecipeFromGroq($userTxt, json_encode($pantryItems));

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'AI failed: ' . $response['message'],
                'data' => null
            ];
        }

        return [
            'success' => true,
            'message' => 'AI Recipe generated successfully.',
            'data' => $response['data']
        ];
    }

    public function saveRecipeService($data, $user)
    {
        $householder = Householder::where('user_id', $user->id)->first();

        if (!$householder) {
            throw new \Exception("User is not a householder.");
        }

        $householderId = $householder->id;

        return DB::transaction(function () use ($data, $householderId) {

            $recipe = Recipe::create([
                'title' => $data['title'],
                'instructions' => $data['instructions'],
                'household_id' => $householderId,
            ]);

            foreach ($data['ingredients'] as $ingredientData) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'item_name' => $ingredientData['item_name'],
                    'quantity' => $ingredientData['quantity'],
                    'unit_id'   => $ingredientData['unit_id'],
                ]);
            }

            return $recipe->load('ingredients');
        });
    }

    public function deleteService($user, $recipeId)
    {
        $householder = Householder::where('user_id', $user->id)->first();
        $admin = Admin::where('user_id', $user->id)->first();

        $recipe = Recipe::where('id', $recipeId)->first();

        if (!$recipe) {
            throw new \Exception("Recipe not found.");
        }

        if (!$admin && $recipe->household_id !== $householder->id) {
            throw new \Exception("Unauthorized: This recipe does not belong to this user.");
        }

        return DB::transaction(function () use ($recipe) {
            RecipeIngredient::where('recipe_id', $recipe->id)->delete();
            $recipe->delete();
            return true;
        });
    }

    public function GetIngredientForRecipeService($recipeId, $user)
    {
        $householder = Householder::where('user_id', $user->id)->first();
        $member = HouseHolderMember::where('user_id', $user->id)->first();

        $userHouseId = $member ? $member->householder_id : $householder->id;

        $recipe = Recipe::where('id', $recipeId)->first();

        if (!$recipe || $recipe->household_id !== $userHouseId) {
            throw new \Exception("This recipe is not accessible by the user.");
        }

        $ingredients = RecipeIngredient::where('recipe_id', $recipeId)->get();

        return [
            'recipe' => $recipe,
            'ingredients' => $ingredients
        ];
    }
}

// namespace App\services;
// use App\Models\Recipe;
// use App\Services\PantryItemServices;
// use App\Services\UseAIService;

// class RecipesService
// {
//     // Recipe service methods would go here
//     function getAllRecipesForHouseHolder($user,$houseHolderIdFromAdmin)
//     {
//         $admin=Admin::Where('user_id',$user->id)->first();
//         $member=HouseHolderMember::where('user_id',$user->id)-first();
//         $householder=Householder::where('user_id',$user->id)->first();
//         if($admin){
//             if($houseHolderIdFromAdmin){
//                 $houseHolder_id=$houseHolderIdFromAdmin;
//             }else{
//                 return Recipe::All();
                
//             }
            
//         }else if($member){
//             $houseHolder_id=$member->$householder_id;
//         }
//         else if ($householder){
//             $houseHolder_id=$householder->id;
//         }
//         $Recipes = Recipe::where('household_id', $houseHolder_id)->get();
        
//         return $Recipes;
//     }
//     public  function   createRecipseByAiASUserWantAndReturnToCLientService($userTxt, $user)
//     {
//         $household_id=$householder->id;
//         $gradientItems=PantryItemServices::GetAllPantryItemsForHouseHolder($household_id);
//         $gradientItem_string=json_encode($gradientItems);
//         $useAIService=new UseAIService();
//         $response=$useAIService->getRecipeFromGroq($userTxt, $gradientItem_string);

//         if(!$response['success']){
//             return [
//                 'success' => false,
//                 'message' => 'Failed to create recipe via AI: '.$response['message'],
//                 'data' => null
//             ];
//         }
//         else if($response['success']){
//             return [
//                 'success' => true,
//                 'message' => 'this Recipe is created by AI as user want if you want to edit please edit it',
//                 'data' => $response['data']
//             ];
//         }

//     }
//     public  function saveRecipeServiceService($data, $user)
//     {
//          try {
//             $householder=$Householder::where('user_id',$user->id);
//             if(!$householder){
//                 throw  new \Exception( "this user is not the householder"); 
//             }
//             $houseHolderId=$householder->id;
       
//             DB::beginTransaction();
    
//             // Save Recipe
//             $recipe = new Recipe();
//             $recipe->title = $data["title"];
//             $recipe->instructions = $data["instructions"];   // fixed typo
//             $recipe->household_id = $houseHolderId;
    
//             if (!$recipe->save()) {
//                 throw new \Exception("Failed to save recipe");
//             }
    
//             // Save Ingredients
//             foreach ($data['ingredients'] as $ingredientData) {
    
//                 $ingredient = new RecipeIngredient();
//                 $ingredient->recipe_id = $recipe->id;
//                 $ingredient->item_name = $ingredientData['item_name'];
//                 $ingredient->quantity  = $ingredientData['quantity'];
//                 $ingredient->unit_id   = $ingredientData['unit_id'];
    
//                 if (!$ingredient->save()) {
//                     throw new \Exception("Failed to save ingredient: ".$ingredientData['item_name']);
//                 }
//             }
    
//             DB::commit();
    
//             return $recipe->load('ingredients');
    
//         } catch (\Exception $e) {
//             DB::rollBack();
//             throw $e; // Send message to controller catch
//         }
//     }

//     // function updateRecipe($id, $data)
//     // {
//     //     $recipe = Recipe::find($id);
//     //     if ($recipe) {
//     //         $recipe->update($data);
//     //         return $recipe;
//     //     }
//     //     return "is not exist";
//     // }
//     function deleteService($user,$recipeId){
//       try{
//             $admin=Admin::where('user_id',$user->id)->firrst();
//             $member=HouseHolderMember::where('user_id',$user->id)->first();
//             $householder=Household::where('user_id',$user->id)->first();
//             if($member){
//                 throw new \Exception('the member can not delete');
//             }
//             $recipe=Recipe::where('recipe_id',$recipe_id)->first();
//             if(!$recipe){
//                 throw new \Exception('sory no recipe for this idea');
//             }
//             if($householder){
//                 if($recipe->househoulder_id !==$householder->householder_id){
//                     throw new \Exception('this item is not for this user ');
//                 }
//             }
//             $recipeIngrediants=RecipeIngredient::where('recipe');
//              DB::beginTransaction();
//              foreach($recipeIngrediants as $recipeIngrediant){
//                 $recipeIngrediant->delete();
//              }
//              $recipe->delete();
//               DB::commit();
//               return $recipe;
//         } catch (\Exception $e) {
//             DB::rollBack();
//             throw $e; // Send message to controller catch
//         }
//     }
//     public function GetIngrediantForRecipeService($recipe_id,$user){
//         $member=user::where('user_id',$user->id);
//         $houseHolder=HouseHolder::where('user_id',$user->id);
//         if($member){
//             $houseHolder_id=$member->householder_id;
//         }
//         else if($houseHolder){
//             $houseHolder_id=$member->id;
//         }
//         $recipe=Recipe::where('id',$recipe_id);

//         if($recipe->householder->id !==$houseHolder_id){
//            throw new \Exception('this recipe is not for this user');
//         }
//         $recipeGrediants=RecipeIngrediet::where('recipe_id',$recipe_id)->get();

//         return ['recipe' => $recipe ,'ingredients' => $recipeGrediants];
        
//     }


    
// }










?>