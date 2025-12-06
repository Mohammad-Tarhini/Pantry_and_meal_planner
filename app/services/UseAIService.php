<?php
namespace App\Services;

use App\AIServices\ChatGptService;
use App\AIServices\GroqService;

class UseAIService
{
    protected $groqService;

    public function __construct()
    {
        $this->groqService = new GroqService();
    }

    public function getAIResponse($userMessage)
    {
        $response = $this->groqService->ask($userMessage);

        // Validate the response structure
        $content = $response['choices'][0]['message']['content'] ?? null;

        // If AI returned empty → FAIL
        if (empty($content)) {
            return [
                'success' => false,
                'message' => 'No valid content returned from AI',
                'data'    => null
            ];
        }

        return [
            'success' => true,
            'message' => 'AI response retrieved successfully',
            'data'    => $content
        ];
    }
     public function getRecipeFromGroq($userTxt, $gradientItem_string, $recipes_string, $familyCount, $repeat = 1, $errormsg = "")
    {
        if ($repeat > 3) {
            return [
                'success' => false,
                'message' => 'Exceeded maximum retry attempts for AI response',
                'data' => null
            ];
        }

        $text1 = "Using the following pantry items and considering their expiration dates: " . $gradientItem_string .
                 ". If recipes already exist, use them; also include new " . $recipes_string . 
                 " if they don't exist. The number of family members is " . $familyCount .
                 ". Create a recipe based on this user request: " . $userTxt .
                 ". Return the recipe in JSON format with title, ingredients (array of objects), and instructions.";

        $text2 = $text1 . " Previous attempt failed with error: " . $errormsg . 
                 ". Ensure the response is valid JSON with title, ingredients, and instructions.";

        $prompt = $repeat === 1 ? $text1 : $text2;

        $response = $this->getAIResponse($prompt);

        if (!$response['success']) {
            return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $recipes_string, $familyCount, $repeat + 1, $response['message']);
        }

        $recipeJson = $response['data'];
        $recipeData = json_decode($recipeJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $recipes_string, $familyCount, $repeat + 1, "Invalid JSON format received from AI");
        }

        if (isset($recipeData['title'], $recipeData['ingredients'], $recipeData['instructions'])) {
            return [
                'success' => true,
                'message' => 'Recipe created successfully',
                'data' => $recipeData
            ];
        }

        return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $recipes_string, $familyCount, $repeat + 1, "Missing required recipe fields in AI response");
    }
    


    // public function getRecipeFromGroq($userTxt, $gradientItem_string,$recipes_string, $repeat = 1, $errormsg = "")
    // {
    //     if ($repeat > 3) {
    //         return [
    //             'success' => false,
    //             'message' => 'Exceeded maximum retry attempts for AI response',
    //             'data' => null
    //         ];
    //     }
    
    //     $text1 = "Using the following pantry items and try to use how have approuch expire_date: " . $gradientItem_string . 
    //               "if those recipes exist please use themand also put new ".$recipes_string." if they  not exist put all news recipes ".
    //               "take care also that tej number of member of this family is ".$familycount.
    //              ", create a recipe based on this user request. If it does not exist, give me Recipe from you according to gradient: " . $userTxt . 
    //              ". Return the recipe in JSON format with title, ingredients in array of contain the object of each gedient i should use, and instructions.";
    
    //     $text2 = $text1 . " I sent before this but there was an error: " . $errormsg . " Please ensure the response is in valid JSON format with the required fields: title, ingredients, and instructions.";
    
    //     $prompt = $repeat === 1 ? $text1 : $text2;
    
    //     $response = $this->getAIResponse($prompt);
    
    //     if (!$response['success']) {
    //         return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $repeat + 1, $response['message']);
    //     }
    
    //     $recipeJson = $response['message'];
    //     $recipeData = json_decode($recipeJson, true);
    
    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $repeat + 1, "Invalid JSON format received from AI");
    //     }
    
    //     if (isset($recipeData['title'], $recipeData['ingredients'], $recipeData['instructions'])) {
    //         return [
    //             'success' => true,
    //             'message' => 'Recipe created successfully',
    //             'data' => $recipeData
    //         ];
    //     } 
    
    //     return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $repeat + 1, "Missing required recipe fields in AI response");
    // }


    public function generateWeakPlaneAccordingToIngredientAndUserText(
        string $userTxt,
        string $gradientItemString,
        int $retry = 1,
        string $errorMsg = ""
    ) {
        // ----- Stop infinite retry -----
        if ($retry > 3) {
                     return [
                         'success' => false,
                         'message' => 'Exceeded maximum retry attempts',
                         'data'    => null
                     ];
                 }
         
                 // ----- Build prompt -----
                 $prompt = "
         Using these items: $gradientItemString  
         AND this user text: $userTxt  
         
         Generate a **weekly meal plan**.
         
         Return ONLY **valid JSON** with this structure EXACTLY:
         
         {
           \"week_plan\": [
               {
                   \"day_of_week\": 0,
                   \"slot\": \"breakfast\",
                   \"recipe\": {
                       \"title\": \"...\",
                       \"instructions\": \"...\",
                       \"gradients\": [
                           { \"item_name\": \"\", \"quantity\": 0, \"unit_id\": 1 }
                       ]
                   }
               }
           ],
           \"items_to_buy\": [
               { \"item_name\": \"\", \"quantity\": 0, \"unit_id\": 1 }
           ]
         }
         
         IMPORTANT:
         - The JSON must NOT include explanations.
         - The JSON must be valid and parsable.
         " . ($retry > 1 ? "\nPrevious AI error: $errorMsg\n" : "");
         
                 // ----- Call AI -----
                 $response = $this->getAIResponse($prompt);
         
                 if (!$response['success']) {
                     return $this->generateWeakPlaneAccordingToIngredientAndUserText(
                         $userTxt,
                         $gradientItemString,
                         $retry + 1,
                         $response['message']
                     );
                 }
         
                 // ----- Attempt to decode JSON -----
                 $jsonText = $response['data'];
                 $recipeData = json_decode($jsonText, true);
         
                 if (json_last_error() !== JSON_ERROR_NONE) {
                     return $this->generateWeakPlaneAccordingToIngredientAndUserText(
                         $userTxt,
                         $gradientItemString,
                         $retry + 1,
                         "Invalid JSON returned by AI"
                     );
                 }
         
                 // ----- Validate structure -----
                 if (
                     !isset($recipeData['week_plan']) ||
                     !isset($recipeData['items_to_buy']) ||
                     !is_array($recipeData['week_plan']) ||
                     !is_array($recipeData['items_to_buy'])
                 ) {
                     return [
                         'success' => false,
                         'message' => 'Invalid structure: week_plan or items_to_buy missing',
                         'data'    => $recipeData
                     ];
                 }
         
                 // Validate week_plan items
                 foreach ($recipeData['week_plan'] as $i => $plan) {
                     if (
                         !isset($plan['day_of_week']) ||
                         !isset($plan['slot']) ||
                         !isset($plan['recipe'])
                     ) {
                         return [
                             'success' => false,
                             'message' => "week_plan[$i] missing fields",
                             'data'    => $plan
                         ];
                     }
         
                     $recipe = $plan['recipe'];
         
                     if (
                         !isset($recipe['title']) ||
                         !isset($recipe['instructions']) ||
                         !isset($recipe['gradients']) ||
                         !is_array($recipe['gradients'])
                     ) {
                         return [
                             'success' => false,
                             'message' => "Invalid recipe structure in week_plan[$i]",
                             'data'    => $recipe
                         ];
                     }
         
                     // Validate gradients
                     foreach ($recipe['gradients'] as $g => $grad) {
                         if (
                             !isset($grad['item_name']) ||
                             !isset($grad['quantity']) ||
                             !isset($grad['unit_id'])
                         ) {
                             return [
                                 'success' => false,
                                 'message' => "Invalid gradient in week_plan[$i].recipe.gradients[$g]",
                                 'data'    => $grad
                             ];
                         }
                     }
                 }
         
                 // ----- SUCCESS -----
                 return [
                     'success' => true,
                     'message' => 'Weekly plan generated successfully',
                     'data'    => [
                         'week_plan'   => $recipeData['week_plan'],
                         'items_to_buy'=> $recipeData['items_to_buy']
                     ]
                 ];
        }
}   

    // public function generateWeakPlaneAccordingToIngredientAndUserText(
    //     $userTxt, 
    //     $gradientItem_string, 
    //     $repeat = 1, 
    //     $errormsg = ""
    // ) {
    //     //  Prevent infinite recursive loops
    //     if ($repeat > 3) {
    //         return [
    //             'success' => false,
    //             'message' => 'Exceeded maximum retry attempts for AI response',
    //             'data' => null
    //         ];
    //     }
    
    //     // ---------- PROMPT BUILDING ----------
    //     $text1 = "Using these items: $gradientItem_string, give me meals for the upcoming week.
    //     Format the response as an ARRAY OF OBJECTS with:
    //     - day_of_week
    //     - slot: ['breakfast', 'lunch', 'dinner']
    //     - recipe: { title, instructions, gradients: [ { item_name, quantity, unit_id } ] }
        
    //     Also, include ANOTHER ARRAY:
    //     - items I should buy (the missing gradients needed to complete all meals).";
    
    //     $text2 = $text1 . " Previous attempt failed with error: " . $errormsg;
    
    //     $prompt = ($repeat === 1) ? $text1 : $text2;
    
    //     // ---------- CALL AI ----------
    //     $response = $this->getAIResponse($prompt);
    
    //     if (!$response['success']) {
    //         return $this->generateWeakPlaneAccordingToIngredientAndUserText(
    //             $userTxt, 
    //             $gradientItem_string, 
    //             $repeat + 1, 
    //             $response['message']
    //         );
    //     }
    
    //     // ---------- JSON PARSING ----------
    //     $recipeJson = $response['message'];
    //     $recipeData = json_decode($recipeJson, true);
    
    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         return $this->generateWeakPlaneAccordingToIngredientAndUserText(
    //             $userTxt,
    //             $gradientItem_string,
    //             $repeat + 1,
    //             "Invalid JSON format received from AI"
    //         );
    //     }
    
    //     // ---------- VALIDATION ----------
    //     if (!is_array($recipeData)) {
    //         return [
    //             'success' => false,
    //             'message' => 'AI returned non-array JSON',
    //             'data' => null
    //         ];
    //     }
    
    //     if (
    //         !isset($recipeData['week_plan']) ||
    //         !isset($recipeData['items_to_buy']) ||
    //         !is_array($recipeData['week_plan']) ||
    //         !is_array($recipeData['items_to_buy'])
    //     ) {
    //         return [
    //             'success' => false,
    //             'message' => 'Invalid AI structure. Expected week_plan and items_to_buy.',
    //             'data' => $recipeData
    //         ];
    //     }
    //     foreach ($recipeData['week_plan'] as $i => $plan) {
    //         if (
    //             !isset($plan['day_of_week']) ||
    //             !isset($plan['slot']) ||
    //             !isset($plan['recipe'])
    //         ) {
    //             return [
    //                 'success' => false,
    //                 'message' => "week_plan[$i] is missing required fields (day_of_week, slot, recipe)",
    //                 'data' => $plan
    //             ];
    //         }
        
    //         // validate recipe object
    //         $recipe = $plan['recipe'];
        
    //         if (
    //             !isset($recipe['title']) ||
    //             !isset($recipe['instructions']) ||
    //             !isset($recipe['gradients']) ||
    //             !is_array($recipe['gradients'])
    //         ) {
    //             return [
    //                 'success' => false,
    //                 'message' => "week_plan[$i].recipe structure is invalid",
    //                 'data' => $recipe
    //             ];
    //         }
        
    //         // validate each gradient inside recipe
    //         foreach ($recipe['gradients'] as $g => $grad) {
    //             if (
    //                 !isset($grad['item_name']) ||
    //                 !isset($grad['quantity']) ||
    //                 !isset($grad['unit_id'])
    //             ) {
    //                 return [
    //                     'success' => false,
    //                     'message' => "week_plan[$i].recipe.gradients[$g] is missing required fields",
    //                     'data' => $grad
    //                 ];
    //             }
    //         }
    //     }

    
    //     // ---------- SUCCESS ----------
    //     return [
    //         'success' => true,
    //         'message' => 'Weekly plan generated successfully',
    //         'data' => [
    //             'week_plan'   => $recipeData['week_plan'],
    //             'items_to_buy' => $recipeData['items_to_buy']
    //         ]
    //     ];
    // }

