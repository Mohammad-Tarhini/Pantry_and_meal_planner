<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\User\TaskController;
use App\Http\Controllers\Admin\TaskController as TaskAdminController;

use App\Http\Controllers\Autho\AuthController;
use App\Http\Controllers\Expense\ExpenseController;
use App\Http\Controllers\Householder\HouseholderController;
use App\Http\Controllers\MealPlan\MealPlanController;
use App\Http\Controllers\pantryItem\PantryItemController;
use App\Http\Controllers\Recipe\RecipeController;
use App\Http\Controllers\ShoppingList\ShoppingListController;

// Public Auth Routes
Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);
Route::post("/logout", [AuthController::class, "logout"]);

// API v0.1 with auth middleware
Route::group(["prefix" => "v0.1", "middleware" => "auth:api"], function () {

    // === Expense Routes ===
    Route::prefix("expense")->group(function () {
        Route::get("/", [ExpenseController::class, "index"]);
        Route::post("/", [ExpenseController::class, "store"]);
        Route::put("/{id}", [ExpenseController::class, "update"]);
        Route::delete("/{id}", [ExpenseController::class, "destroy"]);
    });

    // === Householder Routes ===
    Route::prefix("householder")->group(function () {
        Route::get("/", [HouseholderController::class, "index"]);
        Route::post("/", [HouseholderController::class, "store"]);
    });

    // === Meal Plan Routes ===
    Route::prefix("meal-plan")->group(function () {
        Route::get("/", [MealPlanController::class, "getMealPlans"]);
        Route::post("/", [MealPlanController::class, "AddMealPlan"]);
    });

    // === Pantry Item Routes ===
    Route::prefix("pantry")->group(function () {
        Route::get("/", [PantryItemController::class, "getPantryItems"]);
        Route::post("/", [PantryItemController::class, "AddPlanteryItem"]);
        Route::put("/", [PantryItemController::class, "UpdatePantryItem"]);
        Route::get("/item", [PantryItemController::class, "GetPantryItemById"]);
        Route::delete("/item", [PantryItemController::class, "DeletePantryItemByID"]);
    });

    // === Recipe Routes ===
    Route::prefix("recipe")->group(function () {
        Route::post("/", [RecipeController::class, "saveRecipe"]);
        Route::post("/ai-create", [RecipeController::class, "CreateRecipeByAiReturnToUser"]);
        Route::get("/", [RecipeController::class, "GetAllRecipes"]);
        Route::get("/ingredients", [RecipeController::class, "GetIngrediantForRecipe"]);
        Route::put("/", [RecipeController::class, "updateRecipe"]);
        Route::delete("/", [RecipeController::class, "delete"]);
    });

    // === Shopping List Routes ===
    Route::prefix("shopping-list")->group(function () {
        Route::post("/", [ShoppingListController::class, "PostShopListFromClient"]);
        Route::post("/add-items", [ShoppingListController::class, "AddItemForShopList"]);
        Route::put("/", [ShoppingListController::class, "UpdateShopList"]);
        Route::get("/", [ShoppingListController::class, "GetAllShopList"]);
        Route::get("/items", [ShoppingListController::class, "GetShopListItem"]);
    });
});
