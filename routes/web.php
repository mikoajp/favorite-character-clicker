<?php

use App\Http\Controllers\CharacterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CharacterController::class, 'showHomePage']);

// Temporary debug route
Route::get('/debug-db', function() {
    try {
        // Check if tables exist
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
        echo "<h3>Database Tables:</h3>";
        foreach($tables as $table) {
            echo "- " . $table->name . "<br>";
        }
        
        // Check if games exist
        $gameCount = DB::table('games')->count();
        echo "<h3>Games in database: " . $gameCount . "</h3>";
        
        if ($gameCount > 0) {
            $games = DB::table('games')->orderBy('created_at', 'desc')->limit(5)->get();
            foreach($games as $game) {
                echo "Game ID: {$game->id}, Status: {$game->status}, Session: {$game->session_id}, Created: {$game->created_at}<br>";
            }
        }
        
        // Check current session
        echo "<h3>Current Session ID: " . session()->getId() . "</h3>";
        echo "<h3>Session Data:</h3>";
        $sessionData = session()->all();
        if (empty($sessionData)) {
            echo "No session data found<br>";
        } else {
            foreach($sessionData as $key => $value) {
                echo "{$key}: " . json_encode($value) . "<br>";
            }
        }
        
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString();
    }
});
