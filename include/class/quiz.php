<?php
// filepath: include/class/quiz.php

class QuizException extends Exception {}

class Quiz {
    private int $id;
    private string $title;
    private string $description;
    private ?string $category;
    private ?string $tags;
    private ?string $imageUrl;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $id,
        string $title,
        string $description = '',
        ?string $category = null,
        ?string $tags = null,
        ?string $imageUrl = null,
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->setId($id);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setCategory($category);
        $this->setTags($tags);
        $this->setImageUrl($imageUrl);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getTitle(): string {
        return $this->title;
    }    public function setTitle(string $title): void {
        if (empty(trim($title))) {
            throw new QuizException("Title cannot be empty");
        }else if (strlen($title) > 255) {
            throw new QuizException("Title cannot exceed 255 characters");
        }else if (!preg_match('/^[a-zA-Z0-9\s\-\.,!?\'":&()]+$/', $title)) {
            throw new QuizException("Title contains invalid characters");
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

    public function getCategory(): ?string {
        return $this->category;
    }

    public function setCategory(?string $category): void {
        if ($category !== null && strlen($category) > 100) {
            throw new QuizException("Category cannot exceed 100 characters");
        }
        $this->category = $category ? htmlspecialchars($category, ENT_QUOTES, 'UTF-8') : null;
    }

    public function getTags(): ?string {
        return $this->tags;
    }

    public function setTags(?string $tags): void {
        $this->tags = $tags ? htmlspecialchars($tags, ENT_QUOTES, 'UTF-8') : null;
    }

    public function getImageUrl(): ?string {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void {
        if ($imageUrl !== null && strlen($imageUrl) > 500) {
            throw new QuizException("Image URL cannot exceed 500 characters");
        }
        if ($imageUrl !== null && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new QuizException("Invalid image URL format");
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
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'category' => $this->getCategory(),
            'tags' => $this->getTags(),
            'imageUrl' => $this->getImageUrl(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt()
        ];
    }

    public function getTagsArray(): array {
        return $this->tags ? array_map('trim', explode(',', $this->tags)) : [];
    }
}
