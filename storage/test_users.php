<?php
$users = \App\Models\User::all();
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Role: {$u->role}\n";
}
