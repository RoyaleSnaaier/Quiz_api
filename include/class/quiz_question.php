<?php

class QuizQuestionException extends Exception {}

class QuizQuestion {
    private int $id;
    private int $quizId;
    private string $question;
    private ?int $timeLimit;
    private ?string $imageUrl;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $id,
        int $quizId,
        string $question,
        ?int $timeLimit = null,
        ?string $imageUrl = null,
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->setId($id);
        $this->setQuizId($quizId);
        $this->setQuestion($question);
        $this->setTimeLimit($timeLimit);
        $this->setImageUrl($imageUrl);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        if ($id < -1 || $id == 0) {
            throw new QuizQuestionException("ID must be a positive integer or -1 for new records");
        }
        $this->id = $id;
    }

    public function getQuizId(): int {
        return $this->quizId;
    }

    public function setQuizId(int $quizId): void {
        if ($quizId <= 0) {
            throw new QuizQuestionException("Quiz ID must be a positive integer");
        }
        $this->quizId = $quizId;
    }

    public function getQuestion(): string {
        return $this->question;
    }

    public function setQuestion(string $question): void {
        if (empty(trim($question))) {
            throw new QuizQuestionException("Question cannot be empty");
        } else if (strlen($question) > 500) {
            throw new QuizQuestionException("Question cannot exceed 500 characters");
        }
        $this->question = htmlspecialchars($question, ENT_QUOTES, 'UTF-8');
    }

    public function getTimeLimit(): ?int {
        return $this->timeLimit;
    }

    public function setTimeLimit(?int $timeLimit): void {
        if ($timeLimit !== null && $timeLimit <= 0) {
            throw new QuizQuestionException("Time limit must be a positive number of seconds");
        }
        $this->timeLimit = $timeLimit;
    }

    public function getImageUrl(): ?string {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void {
        if ($imageUrl !== null && strlen($imageUrl) > 500) {
            throw new QuizQuestionException("Image URL cannot exceed 500 characters");
        }
        if ($imageUrl !== null && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new QuizQuestionException("Invalid image URL format");
        }
        $this->imageUrl = $imageUrl;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string {
        return $this->updatedAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'quizId' => $this->getQuizId(),
            'question' => $this->getQuestion(),
            'timeLimit' => $this->getTimeLimit(),
            'imageUrl' => $this->getImageUrl(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt()
        ];
    }
}