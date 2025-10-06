<?php

class application_works
{
    private ?int $works_id;
    private ?int $application_id;
    private ?string $worksFilePath;

    public function __construct(int $works_id, int $application_id, string $worksFilePath) {
        $this->works_id = $works_id;
        $this->application_id = $application_id;
        $this->worksFilePath = $worksFilePath;
    }
    public function getWorksId(): int { return $this->works_id; }
    public function getApplicationId(): string { return $this->application_id; }
    public function getWorksFilePath(): string { return $this->worksFilePath; }




}