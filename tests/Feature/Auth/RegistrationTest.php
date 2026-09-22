<?php

use App\Models\Branch;
use App\Models\User;

test('guests are redirected away from the registration screen', function () {
    $response = $this->get('/register');

    $response->assertRedirect('/login');
});

test('non-admins cannot access the registration screen', function () {
    $staff = User::factory()->create(['role' => 'staff', 'branch_id' => Branch::factory()->create()->id]);

    $response = $this->actingAs($staff)->get('/register');

    $response->assertForbidden();
});

test('admins can create new staff users', function () {
    $admin = User::factory()->create(['role' => 'admin', 'branch_id' => null]);
    $branch = Branch::factory()->create();

    $response = $this->actingAs($admin)->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'staff',
        'branch_id' => $branch->id,
    ]);

    $response->assertRedirect(route('register'));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'staff',
        'branch_id' => $branch->id,
    ]);
});
