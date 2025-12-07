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

   // ===============================
// EXPENSE ROUTES
// ===============================
Route::prefix('expense')->group(function () {
    Route::post('/enter-invoice', [ExpenseController::class, 'EnterInvoice']);
});


// ===============================
// MEAL PLAN ROUTES
// ===============================
Route::prefix('meal-plan')->group(function () {

    Route::post('/create-week-ai', [MealPlanController::class, 'createWeekMealByAi']);

    Route::post('/save-week', [MealPlanController::class, 'saveMealsForWeek']);

    Route::get('/get-meals', [MealPlanController::class, 'getMeals']);
});


// ===============================
// PANTRY ITEM ROUTES
// ===============================
Route::prefix('pantry')->group(function () {

    Route::get('/items', [PantryItemController::class, 'getPantryItems']);

    Route::post('/item', [PantryItemController::class, 'addPantryItem']);

    Route::put('/item/{itemId}', [PantryItemController::class, 'updatePantryItem']);

    Route::get('/item/{itemId}', [PantryItemController::class, 'getPantryItemById']);

    Route::post('/item/{itemId}', [PantryItemController::class, 'deletePantryItem']);
});


// ===============================
// RECIPE ROUTES
// ===============================
Route::prefix('recipe')->group(function () {

    Route::post('/save', [RecipeController::class, 'saveRecipe']);

    Route::post('/create-by-ai', [RecipeController::class, 'CreateRecipeByAiReturnToUser']);

    Route::get('/all', [RecipeController::class, 'GetAllRecipes']);

    Route::get('/ingredients', [RecipeController::class, 'GetIngredientForRecipe']);

    Route::post('/delete', [RecipeController::class, 'delete']);
});


// ===============================
// SHOPPING LIST ROUTES
// ===============================
Route::prefix('shopping-list')->group(function () {

    Route::post('/create', [ShoppingListController::class, 'postShopListFromClient']);

    Route::post('/add-items', [ShoppingListController::class, 'addItemForShopList']);

    Route::post('/update', [ShoppingListController::class, 'updateShopList']);

    Route::get('/all', [ShoppingListController::class, 'getAllShopLists']);

    Route::get('/items', [ShoppingListController::class, 'getShopListItems']);
});
});
