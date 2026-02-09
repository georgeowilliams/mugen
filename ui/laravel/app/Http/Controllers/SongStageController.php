<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SongStageController extends Controller
{
    public function show(string $album, string $song, string $stage)
    {
        return view('song.stage', [
            'albumId' => $album,
            'songId' => $song,
            'stage' => $stage,
            'n8nBaseUrl' => $this->n8nBaseUrl(),
        ]);
    }

    public function approve(Request $request, string $song)
    {
        $payload = $this->validatePayload($request);
        $payload['songId'] = $song;

        return response()->json($this->postToN8n('/webhook/song-approve', $payload));
    }

    public function revise(Request $request, string $song)
    {
        $payload = $this->validatePayload($request);
        $payload['songId'] = $song;
        $payload['revisionNotes'] = $request->input('revisionNotes');

        return response()->json($this->postToN8n('/webhook/song-revise', $payload));
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'stage' => ['required', 'string'],
            'candidateId' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function postToN8n(string $path, array $payload): array
    {
        $baseUrl = $this->n8nBaseUrl();
        $response = Http::timeout(10)->post($baseUrl . $path, $payload);

        return $response->json() ?? ['status' => 'queued'];
    }

    private function n8nBaseUrl(): string
    {
        return rtrim(config('services.n8n.base_url', env('N8N_BASE_URL', 'http://n8n:5678')), '/');
    }
}
