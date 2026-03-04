<?php
echo "Admin 2: " . \App\Models\Customer::withoutGlobalScopes()->where('admin_id', 2)->count() . "\n";
echo "Admin 3: " . \App\Models\Customer::withoutGlobalScopes()->where('admin_id', 3)->count() . "\n";
echo "Total: " . \App\Models\Customer::withoutGlobalScopes()->count() . "\n";
echo "Lat/Lng: " . \App\Models\Customer::withoutGlobalScopes()->whereNotNull('latitude')->whereNotNull('longitude')->count() . "\n";
echo "Active: " . \App\Models\Customer::withoutGlobalScopes()->where('is_active', 1)->count() . "\n";
