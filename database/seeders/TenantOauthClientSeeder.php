<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;
use Stancl\Tenancy\Database\Models\Domain;

class TenantOauthClientSeeder extends Seeder
{
    public function run()
    {
        $tenantIds = $this->command->option('tenants'); // Get tenant ID(s) from command option

        if (!$tenantIds) {
            $this->command->error('No tenant ID provided.');
            return;
        }

        // Ensure tenantIds is an array
        $tenantIds = is_array($tenantIds) ? $tenantIds : [$tenantIds];

        $domains = Domain::whereIn('tenant_id', $tenantIds)
            ->orderBy('id') // Ensure ordering for "first occurrence"
            ->get()
            ->groupBy('tenant_id') // Group by tenant_id
            ->map(fn($group) => $group->first()->domain) // Get first domain per tenant
            ->values() // Remove associative keys if needed
            ->toArray();

        if (empty($domains)) {
            $this->command->error("No domains where found.");
            return;
        }

        $tenantIdsIndex = 0;

        foreach ($domains as $domain) {

            $tenantID = $tenantIds[$tenantIdsIndex];

            $tenantIdsIndex++;

            $protocol = (config('app.env') === 'production') ? 'https://' : 'http://';

            $callback_url = $protocol . $domain . '/callback';

            $client = new ClientRepository();
            $pgrantClient = $client->createPasswordGrantClient(null, 'Default password grant client', $callback_url, 'users');
            $paccessClient = $client->createPersonalAccessClient(null, 'Default personal access client', $callback_url);
            $testClient = $client->create(null, 'Testing access client', $callback_url);

            $this->command->line("<fg=magenta>Tenant:</> <fg=white>$tenantID</>");
            $this->command->line("<fg=cyan>Default password grant client</>");
            $this->command->line("<fg=yellow>Client Id:</> <fg=white>$pgrantClient->id</>" );
            $this->command->line("<fg=yellow>Client Secret:</> <fg=white>$pgrantClient->secret</>" );
            $this->command->line("<fg=cyan>Default personal access client</>");
            $this->command->line("<fg=yellow>Client Id:</> <fg=white>$paccessClient->id</>" );
            $this->command->line("<fg=yellow>Client Secret:</> <fg=white>$paccessClient->secret</>" );
            $this->command->line("<fg=cyan>Default Testing client</>");
            $this->command->line("<fg=yellow>Client Id:</> <fg=white>$testClient->id</>" );
            $this->command->line("<fg=yellow>Client Secret:</> <fg=white>$testClient->secret</>" );
        }
    }
}
