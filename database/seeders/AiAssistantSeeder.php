<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\AiKnowledgeSource;
use App\Models\AiKnowledgeChunk;
use App\Models\AiAvatarProfile;
use App\Models\AiAvatarScenePrompt;
use App\Models\AiChatSession;
use App\Enums\SourceType;
use App\Enums\TrustLevel;
use App\Enums\AiProcessingStatus;
use App\Enums\ChatPlatform;
use App\Enums\ChatSessionStatus;

class AiAssistantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Avatar
        $avatar = AiAvatarProfile::create([
            'name' => 'Aria',
            'slug' => 'aria-gmp-guardian',
            'role_title' => 'GMP Guardian',
            'persona_text' => 'Aria is a strict but helpful AI assistant focused on GMP/CPOB compliance. She answers concisely and professionally.',
            'greeting_text' => 'Halo, saya Aria. Saya di sini untuk memastikan Anda mengikuti standar CPOB dengan benar.',
            'is_active' => true,
        ]);

        // 2. Create Scene Prompt
        AiAvatarScenePrompt::create([
            'avatar_profile_id' => $avatar->id,
            'scene_key' => 'cleanroom_entrance',
            'prompt_title' => 'Gowning Check',
            'prompt_text' => 'Selamat datang di area Gowning. Pastikan Anda telah melepas semua perhiasan dan mencuci tangan sebelum masuk ke ruang ganti.',
            'suggested_questions_json' => ['Bagaimana prosedur cuci tangan?', 'Mengapa perhiasan dilarang?'],
        ]);

        // 3. Create Knowledge Source
        $source = AiKnowledgeSource::create([
            'title' => 'Ringkasan Pedoman CPOB 2024',
            'slug' => 'ringkasan-cpob-2024',
            'source_type' => SourceType::MANUAL,
            'trust_level' => TrustLevel::VERIFIED,
            'parsing_status' => AiProcessingStatus::COMPLETED,
            'indexing_status' => AiProcessingStatus::COMPLETED,
            'is_active' => true,
        ]);

        // 4. Create Chunks
        AiKnowledgeChunk::create([
            'source_id' => $source->id,
            'chunk_index' => 0,
            'section_title' => 'Gowning Dasar',
            'chunk_text' => 'Personil harus mengenakan pakaian pelindung yang sesuai dengan kelas kebersihan ruang kerja. Untuk Kelas B, pakaian harus menutup seluruh tubuh dan tidak melepaskan serat.',
            'embedding_status' => AiProcessingStatus::COMPLETED,
        ]);

        AiKnowledgeChunk::create([
            'source_id' => $source->id,
            'chunk_index' => 1,
            'section_title' => 'Line Clearance',
            'chunk_text' => 'Line clearance dilakukan sebelum proses pengemasan atau pengisian dimulai untuk memastikan tidak ada sisa bahan dari batch sebelumnya yang tertinggal di jalur produksi.',
            'embedding_status' => AiProcessingStatus::COMPLETED,
        ]);

        // 5. Create Sample Session
        AiChatSession::create([
            'user_id' => 1, // Assume admin or main user
            'platform' => ChatPlatform::WEB,
            'session_title' => 'Belajar GMP Dasar',
            'status' => ChatSessionStatus::ACTIVE
        ]);
    }
}
