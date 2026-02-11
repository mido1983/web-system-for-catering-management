<?php
namespace App\Services;

use App\Models\AuditLogModel;

class AuditService
{
    public static function log(?int $actorId, string $action, string $entityType, string $entityId, ?array $before, ?array $after): void
    {
        $data = [
            'actor_user_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_json' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
            'after_json' => $after ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ];
        AuditLogModel::insert($data);
    }
}