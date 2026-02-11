<?php
namespace App\Services;

use App\Core\DB;
use App\Models\RecipeItemModel;

class PlannerService
{
    public static function compute(int $adminId, string $dateFrom, string $dateTo): array
    {
        $sql = "SELECT m.period_start, m.period_end, mi.dish_id, mi.planned_portions, r.id AS recipe_id
                FROM menus m
                INNER JOIN stations s ON s.id = m.station_id
                INNER JOIN menu_versions mv ON mv.menu_id = m.id AND mv.status = 'PUBLISHED'
                INNER JOIN menu_items mi ON mi.menu_version_id = mv.id
                INNER JOIN recipes r ON r.dish_id = mi.dish_id AND r.is_active = 1
                WHERE s.admin_id = :admin_id AND m.period_start <= :date_to AND m.period_end >= :date_from";

        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['admin_id' => $adminId, 'date_from' => $dateFrom, 'date_to' => $dateTo]);
        $rows = $stmt->fetchAll();

        $recipeIds = array_values(array_unique(array_column($rows, 'recipe_id')));
        $recipeItems = RecipeItemModel::listByRecipeIds($recipeIds);

        $byRecipe = [];
        foreach ($recipeItems as $ri) {
            $byRecipe[$ri['recipe_id']][] = $ri;
        }

        $totals = [];
        $from = new \DateTime($dateFrom);
        $to = new \DateTime($dateTo);

        foreach ($rows as $row) {
            $menuStart = new \DateTime($row['period_start']);
            $menuEnd = new \DateTime($row['period_end']);
            $overlapStart = $menuStart > $from ? $menuStart : $from;
            $overlapEnd = $menuEnd < $to ? $menuEnd : $to;
            if ($overlapStart > $overlapEnd) {
                continue;
            }
            $days = (int)$overlapStart->diff($overlapEnd)->format('%a') + 1;

            $recipeId = (int)$row['recipe_id'];
            $planned = (int)$row['planned_portions'];
            $portionTotal = $planned * $days;

            if (empty($byRecipe[$recipeId])) {
                continue;
            }

            foreach ($byRecipe[$recipeId] as $ri) {
                $ingredientId = (int)$ri['ingredient_id'];
                $qty = (int)$ri['qty_per_portion'];
                if (!isset($totals[$ingredientId])) {
                    $totals[$ingredientId] = 0;
                }
                $totals[$ingredientId] += $portionTotal * $qty;
            }
        }

        if (empty($totals)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($totals), '?'));
        $sql2 = "SELECT id, name_he, unit FROM ingredients WHERE id IN ($placeholders)";
        $stmt2 = DB::conn()->prepare($sql2);
        $stmt2->execute(array_keys($totals));
        $ingredients = $stmt2->fetchAll();

        $result = [];
        foreach ($ingredients as $ing) {
            $result[] = [
                'ingredient_id' => $ing['id'],
                'name_he' => $ing['name_he'],
                'unit' => $ing['unit'],
                'total_qty' => $totals[(int)$ing['id']],
            ];
        }

        return $result;
    }
}
