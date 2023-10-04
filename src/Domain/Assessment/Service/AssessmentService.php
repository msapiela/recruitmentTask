<?php

namespace Domain\Assessment\Service;

use DateTime;
use Domain\Assessment\Model\Assessment;
use Domain\Assessment\Model\Client;
use Domain\Assessment\Model\Standard;
use Domain\Assessment\Model\Supervisor;
use Exception;

class AssessmentService
{

    /**
     * @throws Exception
     */
    public function lockAssessmentBySuspension(Assessment $assessment): void
    {
        if (!$assessment->isLocked() && !$assessment->isExpired()) {
            $assessment->lockBySuspension();
        } else {
            throw new \Exception('Assessment cannot be locked by suspension.');
        }
    }

    public function unlockAssessment(Assessment $assessment): void
    {
        $assessment->unlock();
    }

    /**
     * @throws Exception
     */
    public function setPositiveRating(Assessment $assessment): void
    {
        if (!$assessment->isLocked()) {
            $assessment->setPositiveRating();
        } else {
            throw new Exception('Assessment is locked; rating cannot be set.');
        }
    }

    /**
     * @throws Exception
     */
    public function setNegativeRating(Assessment $assessment): void
    {
        if (!$assessment->isLocked()) {
            $assessment->setNegativeRating();
        } else {
            throw new Exception('Assessment is locked; rating cannot be set.');
        }
    }

    public function hasAssessmentExpired(Assessment $assessment): bool
    {
        return $assessment->isExpired();
    }

    public function canUnlockSuspendedAssessment(Assessment $assessment): bool
    {
        return $assessment->isLockedBySuspension();
    }

    /**
     * @throws Exception
     */
    public function unlockSuspendedAssessment(Assessment $assessment): void
    {
        if ($this->canUnlockSuspendedAssessment($assessment)) {
            $assessment->unlock();
        } else {
            throw new Exception('Suspended assessment cannot be unlocked.');
        }
    }

    /**
     * @throws Exception
     */
    public function withdrawAssessment(Assessment $assessment): void
    {
        if (!$assessment->isLocked()) {
            $assessment->lockByWithdrawn();
        } else {
            throw new \Exception('Assessment is locked; withdrawal not allowed.');
        }
    }

    public function lockAssessment(Assessment $assessment)
    {
    }

    /**
     * @throws Exception
     */
    public function lockAssessmentByWithdrawn(Assessment $assessment)
    {
        $assessment->lockByWithdrawn();
    }

    public function setEvaluationDate(Assessment $assessment, DateTime $evaluationDate)
    {
    }

    public function addAssessment(Client $client, Assessment $assessment1)
    {
    }

    public function conductSubsequentEvaluation(Assessment $assessment, DateTime $param)
    {
    }

    public function setStandard(Assessment $assessment1, Standard $standard)
    {
    }

    public function createAssessmentWithEvaluation(string $description, Supervisor $supervisor, array $evaluationData):Assessment
    {
        return new Assessment();
    }

    public function createAssessmentInStandard(array $array, Standard $standard):Assessment
    {
        return new Assessment();
    }

    public function replaceAssessment(Assessment $assessment1, Assessment $assessment2)
    {
    }
}