<?php
namespace App\Models;

use App\Core\DB;

class RecipeItemModel
{
    public static function listByRecipeIds(array $recipeIds): array
    {
        if (empty($recipeIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($recipeIds), '?'));
        $sql = "SELECT recipe_id, ingredient_id, qty_per_portion FROM recipe_items WHERE recipe_id IN ($placeholders)";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($recipeIds);
        return $stmt->fetchAll();
    }
}