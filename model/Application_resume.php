<?php

class application_resume
{
    private ?int $resume_id;
    private ?int $application_id;
    private ?string $resumeFilePath;

    public function __construct(int $resume_id, int $application_id, ?string $resumeFilePath) {
        $this->resume_id = $resume_id;
        $this->application_id = $application_id;
        $this->resumeFilePath = $resumeFilePath;
    }

    public function getResumeId(): int { return $this->resume_id; }
    public function getApplicationId(): int { return $this->application_id; }
    public function getResumeFilePath(): string { return $this->resumeFilePath; }

}