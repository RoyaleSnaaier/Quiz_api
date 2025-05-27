<?php
// filepath: include/class/quiz.php

class QuizException extends Exception {}

class Quiz {
    private int $id;
    private string $title;
    private string $description;
    private string $created_at;
    private string $updated_at;

    public function __construct(
        int $id,
        string $title,
        string $description = '',
        string $created_at = '',
        string $updated_at = ''
    ) {
        $this->setId($id);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        if (empty(trim($title))) {
            throw new QuizException("Title cannot be empty");
        }else if (strlen($title) > 255) {
            throw new QuizException("Title cannot exceed 255 characters");
        }else if (!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
            throw new QuizException("Title can only contain alphanumeric characters and spaces");
        }
        $this->title = $title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        if (strlen($description) > 1000) {
            throw new QuizException("Description cannot exceed 1000 characters");
        } else if (!preg_match('/^[a-zA-Z0-9\s.,!?\'"-]*$/', $description)) {
            throw new QuizException("Description can only contain alphanumeric characters, spaces, and basic punctuation");
        }
        $this->description = $description;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
