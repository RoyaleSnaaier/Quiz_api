<?php

class AnswerException extends Exception {}

class Answer {
    private int $id;
    private int $quizId;
    private int $questionId;
    private string $answerText;
    private bool $isCorrect;
    private ?string $imageUrl;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $id,
        int $quizId,
        int $questionId,
        string $answerText,
        bool $isCorrect = false,
        ?string $imageUrl = null,
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->setId($id);
        $this->setQuizId($quizId);
        $this->setQuestionId($questionId);
        $this->setAnswerText($answerText);
        $this->setIsCorrect($isCorrect);
        $this->setImageUrl($imageUrl);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        if ($id < -1 || $id == 0) {
            throw new AnswerException("ID must be a positive integer or -1 for new records");
        }
        $this->id = $id;
    }

    public function getQuizId(): int {
        return $this->quizId;
    }

    public function setQuizId(int $quizId): void {
        if ($quizId <= 0) {
            throw new AnswerException("Quiz ID must be a positive integer");
        }
        $this->quizId = $quizId;
    }

    public function getQuestionId(): int {
        return $this->questionId;
    }

    public function setQuestionId(int $questionId): void {
        if ($questionId <= 0) {
            throw new AnswerException("Question ID must be a positive integer");
        }
        $this->questionId = $questionId;
    }

    public function getAnswerText(): string {
        return $this->answerText;
    }

    public function setAnswerText(string $answerText): void {
        if (empty(trim($answerText))) {
            throw new AnswerException("Answer text cannot be empty");
        } else if (strlen($answerText) > 500) {
            throw new AnswerException("Answer text cannot exceed 500 characters");
        }
        $this->answerText = htmlspecialchars($answerText, ENT_QUOTES, 'UTF-8');
    }

    public function getIsCorrect(): bool {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): void {
        $this->isCorrect = $isCorrect;
    }

    public function getImageUrl(): ?string {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void {
        if ($imageUrl !== null && strlen($imageUrl) > 500) {
            throw new AnswerException("Image URL cannot exceed 500 characters");
        }
        if ($imageUrl !== null && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new AnswerException("Invalid image URL format");
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
            'questionId' => $this->getQuestionId(),
            'answerText' => $this->getAnswerText(),
            'isCorrect' => $this->getIsCorrect(),
            'imageUrl' => $this->getImageUrl(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt()
        ];
    }
}
