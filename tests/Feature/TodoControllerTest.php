<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Todo;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate'); // Ensure migrations are applied for testing
    }

    public function test_todos_can_be_filtered_by_status()
    {
        $todo = Todo::create([
            'title' => 'Test Todo',
            'details' => 'Test Details',
            'status' => 'completed'
        ]);
    
        $response = $this->getJson('/api/todos?status=completed');
        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'completed']);
    }
    
    public function test_todos_can_be_searched_by_title_and_details()
    {
        $todo = Todo::create([
            'title' => 'Test Todo',
            'details' => 'Test Details',
            'status' => 'not_started'
        ]);
    
        $response = $this->getJson('/api/todos?search=Test');
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Todo']);
    }




    public function test_todo_can_be_created()
{
    $data = [
        'title' => 'New Todo',
        'details' => 'Todo Details',
        'status' => 'not_started'
    ];

    $response = $this->postJson('/api/todos', $data);
    $response->assertStatus(201)
             ->assertJsonFragment(['title' => 'New Todo']);
}


public function test_todo_can_be_updated()
{
    $todo = Todo::create([
        'title' => 'Old Todo',
        'details' => 'Old Details',
        'status' => 'not_started'
    ]);

    $data = [
        'title' => 'Updated Todo',
        'details' => 'Updated Details',
        'status' => 'in_progress'
    ];

    $response = $this->putJson("/api/todos/{$todo->id}", $data);
    $response->assertStatus(200)
             ->assertJsonFragment(['title' => 'Updated Todo']);
}



public function test_todo_can_be_deleted()
{
    $todo = Todo::create([
        'title' => 'Todo to delete',
        'details' => 'Delete me',
        'status' => 'completed'
    ]);

    $response = $this->deleteJson("/api/todos/{$todo->id}");
    $response->assertStatus(200);
}





}
