<?php
namespace App\Models;

use App\Core\DB;

class AuditLogModel
{
    public static function insert(array $data): void
    {
        $sql = 'INSERT INTO audit_log (actor_user_id, action, entity_type, entity_id, before_json, after_json, ip, user_agent, created_at)
                VALUES (:actor_user_id, :action, :entity_type, :entity_id, :before_json, :after_json, :ip, :user_agent, NOW())';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($data);
    }

    public static function list(array $filters, ?array $actorIds = null): array
    {
        $params = [];
        $where = '1=1';

        if ($actorIds && count($actorIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($actorIds), '?'));
            $where .= " AND actor_user_id IN ($placeholders)";
            foreach ($actorIds as $id) {
                $params[] = (int)$id;
            }
        }
        if (!empty($filters['user_id'])) {
            $where .= ' AND actor_user_id = ?';
            $params[] = (int)$filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $where .= ' AND action = ?';
            $params[] = $filters['action'];
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = 'SELECT id, actor_user_id, action, entity_type, entity_id, created_at
                FROM audit_log WHERE ' . $where . ' ORDER BY created_at DESC LIMIT 300';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
