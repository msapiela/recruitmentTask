<?php
namespace Domain\Assessment\Model;

use DateTime;

class Assessment {
    private int $id;
    private DateTime $evaluationDate;
    private Standard $standard;
    private Client $client;
    private Supervisor $supervisor;
    private bool $locked = false;
    private bool $lockedBySuspension = false;
    private bool $lockedByWithdrawn = false;
    private bool $expired = false;
    private bool $positiveRating = false;
    private bool $negativeRating = false;
    public bool $isActive = true;
    private string $description = '';

    public function __construct(
        int $id,
        DateTime $evaluationDate,
        Standard $standard,
        Client $client,
        Supervisor $supervisor
    ) {
        $this->id = $id;
        $this->evaluationDate = $evaluationDate;
        $this->standard = $standard;
        $this->client = $client;
        $this->supervisor = $supervisor;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvaluationDate(): DateTime
    {
        return $this->evaluationDate;
    }

    public function getStandard(): Standard
    {
        return $this->standard;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getSupervisor(): Supervisor
    {
        return $this->supervisor;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): void
    {
        if (!$this->isLocked() && !$this->isExpired()) {
            $this->locked = true;
        }
    }

    public function isLockedBySuspension(): bool
    {
        return $this->lockedBySuspension;
    }

    public function isLockedByWithdrawn(): bool
    {
        return $this->lockedByWithdrawn;
    }

    public function isExpired(): bool
    {
        $expirationDate = clone $this->evaluationDate;
        $expirationDate->modify('+365 days');

        $currentDate = new DateTime();

        return $currentDate > $expirationDate;
    }

    public function setPositiveRating(): void
    {
        if (!$this->isLocked()) {
            $this->positiveRating = true;
        }
    }

    public function setNegativeRating(): void
    {
        if (!$this->isLocked()) {
            $this->negativeRating = true;
        }
    }

    public function hasPositiveRating(): bool
    {
        return $this->positiveRating;
    }

    public function hasNegativeRating(): bool
    {
        return $this->negativeRating;
    }

    public function lockBySuspension() {
        if (!$this->isLockedByWithdrawn() && !$this->isExpired()) {
            $this->locked = true;
            $this->lockedBySuspension = true;
            $this->lockedByWithdrawn = false; // Unlock if previously locked by withdrawal
        } else {
            throw new \Exception('Assessment cannot be locked by suspension.');
        }
    }

    /**
     * @throws \Exception
     */
    public function lockByWithdrawn(): void
    {
        if (!$this->isLockedBySuspension() && !$this->isExpired()) {
            $this->locked = true;
            $this->lockedByWithdrawn = true;
            $this->lockedBySuspension = false; // Unlock if previously locked by suspension
        } else {
            throw new \Exception('Assessment cannot be locked by withdrawal.');
        }
    }

    public function unlock(): void
    {
        $this->locked = false;
        $this->lockedBySuspension = false;
        $this->lockedByWithdrawn = false;
    }

    public function setEvaluationDate(DateTime $param)
    {
    }

    public function isActive()
    {
        return $this->isActive;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setSupervisor(Supervisor $supervisor): void
    {
        $this->supervisor = $supervisor;
    }

    public function getLockDescription():string
    {
        return 'locked';
    }

}