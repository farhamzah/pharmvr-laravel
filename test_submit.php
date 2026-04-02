<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$token = $user->createToken('test')->plainTextToken;
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$attempt = \App\Models\AssessmentAttempt::latest()->first();
if (!$attempt) die("No attempt found.\n");

$questions = $attempt->assessment->questions()->with('options')->take(3)->get();
$answers = [];
foreach ($questions as $q) {
    if ($q->options->count() > 0) {
        $answers[] = [
            'question_id' => $q->id,
            'option_id' => $q->options->first()->id
        ];
    }
}

$req = \Illuminate\Http\Request::create('/api/v1/assessment-attempts/' . $attempt->id . '/submit', 'POST', [], [], [], [], json_encode(['answers' => $answers]));
$req->headers->set('Authorization', "Bearer $token");
$req->headers->set('Accept', 'application/json');
$req->headers->set('Content-Type', 'application/json');

$res = $httpKernel->handle($req);

echo "STATUS: " . $res->getStatusCode() . "\n";
$decoded = json_decode($res->getContent(), true);
if (isset($decoded['exception'])) {
    echo "EXCEPTION: " . $decoded['exception'] . "\n";
    echo "MESSAGE: " . $decoded['message'] . "\n";
} else {
    echo "BODY:\n" . substr($res->getContent(), 0, 800) . "\n";
}
