<?php

namespace Bunny\Storage;

class FileInfo
{
    private string $guid;
    private string $path;
    private string $name;
    private int $size;
    private bool $isDirectory;
    private string $checksum;
    private \DateTimeImmutable $dateCreated;
    private \DateTimeImmutable $dateModified;

    /**
     * @param array<string, string|int|bool> $data
     */
    public function __construct(array $data)
    {
        $this->guid = (string) $data['Guid'];
        $this->path = (string) $data['Path'];
        $this->name = (string) $data['ObjectName'];
        $this->size = (int) $data['Length'];
        $this->isDirectory = (bool) $data['IsDirectory'];
        $this->checksum = \strtolower((string) $data['Checksum']);

        $dateCreated = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.v', (string) $data['DateCreated']);
        if (false === $dateCreated) {
            throw new Exception('Invalid DateCreated for file '.$this->path);
        }

        $dateModified = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.v', (string) $data['LastChanged']);
        if (false === $dateModified) {
            throw new Exception('Invalid LastChanged for file '.$this->path);
        }

        $this->dateCreated = $dateCreated;
        $this->dateModified = $dateModified;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function getDateCreated(): \DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function getDateModified(): \DateTimeImmutable
    {
        return $this->dateModified;
    }
}
