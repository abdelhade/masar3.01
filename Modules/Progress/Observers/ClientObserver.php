<?php

namespace Modules\Progress\Observers;

use Modules\Progress\Models\Client;
use Modules\Progress\Services\ActivityLogService;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        ActivityLogService::created($client, [
            'name' => $client->cname,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
        ], 'clients');
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        $changes = $client->getChanges();
        $original = $client->getOriginal();
        
        $properties = [];
        foreach ($changes as $field => $newValue) {
            if ($field !== 'updated_at') {
                $properties[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue
                ];
            }
        }
        
        if (!empty($properties)) {
            ActivityLogService::updated($client, $properties, 'clients');
        }
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        ActivityLogService::deleted($client, [
            'name' => $client->cname,
            'email' => $client->email,
            'phone' => $client->phone,
        ], 'clients');
    }
}
