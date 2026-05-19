<?php
/**
 * Class Book
 * Menerapkan: Class, Property, Method, Constructor, Encapsulation, Getter/Setter
 */
class Book {
    private string $bookId;
    private string $title;
    private string $author;
    private string $category;
    private float  $price;
    private int    $stock;
    private string $isbn;
    private string $description;

    public function __construct(
        string $bookId,
        string $title,
        string $author,
        string $category,
        float  $price,
        int    $stock,
        string $isbn = '',
        string $description = ''
    ) {
        $this->bookId      = $bookId;
        $this->title       = $title;
        $this->author      = $author;
        $this->category    = $category;
        $this->price       = $price;
        $this->stock       = $stock;
        $this->isbn        = $isbn;
        $this->description = $description;
    }

    // Getters
    public function getBookId(): string      { return $this->bookId; }
    public function getTitle(): string       { return $this->title; }
    public function getAuthor(): string      { return $this->author; }
    public function getCategory(): string    { return $this->category; }
    public function getPrice(): float        { return $this->price; }
    public function getStock(): int          { return $this->stock; }
    public function getIsbn(): string        { return $this->isbn; }
    public function getDescription(): string { return $this->description; }

    // Setters
    public function setTitle(string $title): void       { $this->title = $title; }
    public function setAuthor(string $author): void     { $this->author = $author; }
    public function setCategory(string $cat): void      { $this->category = $cat; }
    public function setPrice(float $price): void        { $this->price = max(0, $price); }
    public function setStock(int $stock): void          { $this->stock = max(0, $stock); }
    public function setDescription(string $desc): void  { $this->description = $desc; }

    // Methods
    public function reduceStock(int $qty): bool {
        if ($this->stock >= $qty) {
            $this->stock -= $qty;
            return true;
        }
        return false;
    }

    public function isAvailable(): bool {
        return $this->stock > 0;
    }

    public function getFormattedPrice(): string {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function toArray(): array {
        return [
            'id'          => $this->bookId,
            'title'       => $this->title,
            'author'      => $this->author,
            'category'    => $this->category,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'isbn'        => $this->isbn,
            'description' => $this->description,
            'available'   => $this->isAvailable(),
        ];
    }
}
