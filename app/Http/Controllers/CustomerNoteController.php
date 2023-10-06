<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerNoteController extends Controller
{
    public function submit(Request $request)
    {
        $notes = $request->input('notes');

        $parsedData = $this->submitToAI($notes);
        $parsedData["notes"] = $notes;

        return view('customer.pests', ['data' => $parsedData]);
    }

    private function submitToAI(string $notes): array
    {
        $authkey = env('OPEN_AI_API_KEY');
        $url = "https://api.openai.com/v1/chat/completions";

        $requestData = [
            "model" => "gpt-3.5-turbo",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You will be provided with unstructured data, and your task is to parse pest names and their location into JSON"
                ],
                [
                    "role" => "user",
                    "content" => $notes
                ]
                ],
            "temperature" => 0,
            "max_tokens" => 256,
            "top_p" => 1,
            "frequency_penalty" => 0,
            "presence_penalty" => 0
        ];

        $response = Http::withToken($authkey)
            ->accept('application/json')
            ->post($url, $requestData);
        Log::info(json_decode($response->json()["choices"][0]["message"]["content"], true));
        return json_decode($response->json()["choices"][0]["message"]["content"], true);
    }
}
