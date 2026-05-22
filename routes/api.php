<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/chat', function (Request $request) {
    $apiKey = env('GOOGLE_API_KEY');
    $prompt = $request->input('prompt');
    
    $payload = [
        'contents' => [
            [
                'parts' => []
            ]
        ]
    ];

    // Add text prompt
    $payload['contents'][0]['parts'][] = ['text' => $prompt];

    // If file uploaded, add it
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $fileContent = base64_encode(file_get_contents($file->getRealPath()));
        
        $payload['contents'][0]['parts'][] = [
            'inlineData' => [
                'mimeType' => $mimeType,
                'data' => $fileContent
            ]
        ];
    }

    $response = Http::withoutVerifying()->post(
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
        $payload
    )->json();
    
    return response()->json($response);
});