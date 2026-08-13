<?php

namespace Tests\Feature\Security;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class CandidateDocumentValidationTest extends TestCase
{
    public function test_candidate_cannot_upload_an_executable_as_a_photo(): void
    {
        $response = $this->postJson($this->uploadUrl(), [
            'file_foto' => UploadedFile::fake()->create(
                'candidate.jpg',
                10,
                'application/x-msdownload'
            ),
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('file_foto');
    }

    public function test_candidate_cannot_upload_an_oversized_cv(): void
    {
        $response = $this->postJson($this->uploadUrl(), [
            'file_cv' => UploadedFile::fake()->create('candidate.pdf', 5121, 'application/pdf'),
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('file_cv');
    }

    private function uploadUrl(): string
    {
        return route('pendaftaran.api.token.jadwal-interview.dokumen.upload', [
            'token' => Str::random(64),
            'jadwalInterviewKandidat' => (string) Str::uuid(),
        ]);
    }
}
