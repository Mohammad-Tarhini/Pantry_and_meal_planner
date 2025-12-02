<?php
 
namespace App\services;
use App\Models\Recipe;
use App\Services\PantryItemServices;
use App\Services\UseAIService;

class RecipesService
{
    // Recipe service methods would go here
    function getAllRecipesForHouseHolder($userId)
    {
        $Recipes = Recipe::where('household_id', $userId)->get();
        return $Recipes;
    }
    public static function   createRecipseByAiASUserWantAndReturnToCLient($userTxt, $household_id)
    {
        $gradientItems=PantryItemServices::GetAllPantryItemsForHouseHolder($household_id);
        $gradientItem_string=json_encode($gradientItems);
        $useAIService=new UseAIService();
        $response=$useAIService->getRecipeFromGroq($userTxt, $gradientItem_string);

        if(!$response['success']){
            return [
                'success' => false,
                'message' => 'Failed to create recipe via AI: '.$response['message'],
                'data' => null
            ];
        }
        else if($response['success']){
            return [
                'success' => true,
                'message' => 'this Recipe is created by AI as user want if you want to edit please edit it',
                'data' => $response['data']
            ];
        }

    }
    public static function saveRecipeService($data, $houseHolderId)
    {
        try {
            DB::beginTransaction();
    
            // Save Recipe
            $recipe = new Recipe();
            $recipe->title = $data["title"];
            $recipe->instructions = $data["instructions"];   // fixed typo
            $recipe->household_id = $houseHolderId;
    
            if (!$recipe->save()) {
                throw new \Exception("Failed to save recipe");
            }
    
            // Save Ingredients
            foreach ($data['ingredients'] as $ingredientData) {
    
                $ingredient = new RecipeIngredient();
                $ingredient->recipe_id = $recipe->id;
                $ingredient->item_name = $ingredientData['item_name'];
                $ingredient->quantity  = $ingredientData['quantity'];
                $ingredient->unit_id   = $ingredientData['unit_id'];
    
                if (!$ingredient->save()) {
                    throw new \Exception("Failed to save ingredient: ".$ingredientData['item_name']);
                }
            }
    
            DB::commit();
    
            return $recipe->load('ingredients');
    
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Send message to controller catch
        }
    }

    function updateRecipe($id, $data)
    {
        $recipe = Recipe::find($id);
        if ($recipe) {
            $recipe->update($data);
            return $recipe;
        }
        return "is not exist";
    }
    function InsertRecipeAfterCreateByAi($data)
    {
        // Logic to insert a recipe



    }
    function InsertRecipeFromClientSide($data)
    {
        // Logic to insert a recipe from client side
        $Recipe=new Recipe();
        $Recipe->title=$data['title'];
        $Recipe->ingredients=$data['ingredients'];
        $Recipe->instructions=$data['instructions'];
        $Recipe->household_id=$data['household_id'];
        $Recipe->save();
        return $Recipe;

    }



    
}










?>