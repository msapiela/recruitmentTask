<?php

namespace Tests\Assessment;

use DateTime;
use Domain\Assessment\Model\Client;
use Domain\Assessment\Model\Standard;
use Domain\Assessment\Model\Supervisor;
use Exception;
use PHPUnit\Framework\TestCase;
use Domain\Assessment\Model\Assessment;
use Domain\Assessment\Service\AssessmentService;

class AssessmentServiceTest extends TestCase {
    public function testCreateAssessmentWithEvaluation() {
        $assessmentService = new AssessmentService();

        $description = "Assessment description";
        $supervisor = new Supervisor();
        $evaluationData = [
            'criteria1' => 'Evaluation criteria 1',
            'criteria2' => 'Evaluation criteria 2',
        ];

        $assessment = $assessmentService->createAssessmentWithEvaluation($description, $supervisor, $evaluationData);

        $this->assertInstanceOf(Assessment::class, $assessment);

        $this->assertEquals($description, $assessment->getDescription());
        $this->assertEquals($supervisor, $assessment->getSupervisor());

        $this->assertEquals($evaluationData, $assessment->getEvaluationDate());
    }

    public function testEvaluationIsCarriedOutBySupervisor() {
        $assessmentService = new AssessmentService();

        $description = "Assessment description";
        $supervisor = new Supervisor();
        $evaluationData = [
            'criteria1' => 'Evaluation criteria 1',
            'criteria2' => 'Evaluation criteria 2',
        ];

        $assessment = $assessmentService->createAssessmentWithEvaluation($description, $supervisor, $evaluationData);

        $this->assertSame($supervisor, $assessment->getSupervisor());
    }

    public function testAssessmentIsCarriedOutInIndicatedStandard() {
        $assessmentService = new AssessmentService();
        $standard = new Standard([/* Initialize standard data */]);

        $assessment = $assessmentService->createAssessmentInStandard([], $standard);

        $this->assertSame($standard, $assessment->getStandard());
    }

    public function testClientMustHaveActiveContractWithSupervisor()
    {
        $client = new Client(/* Initialize client data */);
        $supervisor = new Supervisor(/* Initialize supervisor data */);
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $exceptionThrown = false;
        try {
            $assessmentService->addAssessment($client, $assessment);
        } catch (Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testSupervisorMustHaveAuthorityForStandard() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $supervisor = $this->createMock(Supervisor::class);
        $standard = $assessment->getStandard(); // Get the assessment's standard

        $supervisor->expects($this->once())
            ->method('hasAuthorityForStandard')
            ->with($standard)
            ->willReturn(true);

        $assessment->setSupervisor($supervisor);

        $exceptionThrown = false;
        try {
            $assessmentService->lockAssessmentBySuspension($assessment, 'Suspended');
        } catch (Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertFalse($exceptionThrown);
    }

    /**
     * @throws Exception
     */
    public function testAssessmentCanHavePositiveOrNegativeRatings() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $assessmentService->setPositiveRating($assessment);

        $this->assertTrue($assessment->hasPositiveRating());
        $this->assertFalse($assessment->hasNegativeRating());

        $assessmentService->setNegativeRating($assessment);

        $this->assertFalse($assessment->hasPositiveRating());
        $this->assertTrue($assessment->hasNegativeRating());
    }

    public function testAssessmentExpiresAfter365Days() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $evaluationDate = new DateTime();
        $evaluationDate->modify('-365 days');
        $assessmentService->setEvaluationDate($assessment, $evaluationDate);

        $hasExpired = $assessmentService->hasAssessmentExpired($assessment);

        $this->assertTrue($hasExpired);
    }

    /**
     * @throws Exception
     */
    public function testAssessmentCanBeLockedBySuspensionOrWithdrawn() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $assessmentService->lockAssessmentBySuspension($assessment, 'Locked');

        $this->assertTrue($assessment->isLockedBySuspension());

        $assessmentService->unlockAssessment($assessment);

        $assessmentService->lockAssessmentByWithdrawn($assessment);

        $this->assertTrue($assessment->isLockedByWithdrawn());
    }

    /**
     * @throws Exception
     */
    public function testSuspendedAssessmentCanBeUnlocked() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $assessmentService->lockAssessmentBySuspension($assessment);

        $assessmentService->unlockAssessment($assessment);

        $this->assertFalse($assessment->isLocked());
    }

    /**
     * @throws Exception
     */
    public function testSuspendedAssessmentCanBeWithdrawn() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $assessmentService->lockAssessmentBySuspension($assessment, 'Locked by suspension');

        $assessmentService->lockAssessmentByWithdrawn($assessment);

        $this->assertTrue($assessment->isLockedByWithdrawn());
    }

    /**
     * @throws Exception
     */
    public function testWithdrawnAssessmentCannotBeUnlockedOrChangedToSuspension() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessmentService = new AssessmentService();

        $assessmentService->lockAssessmentByWithdrawn($assessment);

        $exceptionThrown = false;
        try {
            $assessmentService->unlockAssessment($assessment);
        } catch (Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($assessment->isLockedByWithdrawn());
        $this->assertTrue($exceptionThrown);

        $exceptionThrown = false;
        try {
            $assessmentService->lockAssessmentBySuspension($assessment, 'Suspend');
        } catch (Exception $e) {
            $exceptionThrown = true;
        }

        // Assert that the assessment is still locked by withdraw
        $this->assertTrue($assessment->isLockedByWithdrawn());
        $this->assertTrue($exceptionThrown);
    }


    public function testExpiredAssessmentCannotBeLocked() {
        $assessment = new Assessment(/* Initialize assessment data */);
        $assessment->setEvaluationDate(new \DateTime('2020-01-01'));
        $assessmentService = new AssessmentService();

        $exceptionThrown = false;
        try {
            $assessmentService->lockAssessmentBySuspension($assessment);
        } catch (Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($assessment->isExpired());
        $this->assertTrue($exceptionThrown);
    }

    public function testCannotLockLockedAssessment() {
        $assessment = new Assessment(/* Provide necessary data */);
        $assessmentService = new AssessmentService();
        $assessmentService->lockAssessment($assessment);

        $this->expectException(Exception::class);
        $assessmentService->lockAssessment($assessment);
    }

    /**
     * @throws Exception
     */
    public function testChangingSuspensionToWithdrawnIsAllowed() {
        $assessment = new Assessment(/* Provide necessary data */);
        $assessmentService = new AssessmentService();
        $assessmentService->lockAssessmentBySuspension($assessment);

        $assessmentService->lockAssessmentByWithdrawn($assessment);

        $this->assertTrue($assessment->isLockedByWithdrawn());
    }

    /**
     * @throws Exception
     */
    public function testAssessmentLockContainsDescriptiveInformation() {
        $assessment = new Assessment(/* Provide necessary data */);
        $assessmentService = new AssessmentService();

        $assessmentService->lockAssessmentBySuspension($assessment, 'Suspended for review');

        $this->assertEquals('Suspended for review', $assessment->getLockDescription());
    }

    public function testReplaceAssessmentInSameStandard() {
        $assessment1 = new Assessment(/* Provide necessary data */);
        $assessment2 = new Assessment(/* Provide necessary data */);
        $standard = new Standard(/* Initialize standard data */);
        $assessmentService = new AssessmentService();

        $assessmentService->setStandard($assessment1, $standard);
        $assessmentService->setStandard($assessment2, $standard);

        $assessmentService->replaceAssessment($assessment1, $assessment2);

        $this->assertFalse($assessment1->isActive());
        $this->assertTrue($assessment2->isActive());
    }

    public function testNewlyObtainedAssessmentReplacesCurrentOne() {
        $client = new Client(/* Initialize client data */);
        $assessmentService = new AssessmentService();
        $assessment1 = new Assessment(/* Provide necessary data for assessment 1 */);
        $assessment2 = new Assessment(/* Provide necessary data for assessment 2 */);

        $assessmentService->addAssessment($client, $assessment1);
        $assessmentService->addAssessment($client, $assessment2);

        $this->assertFalse($assessment1->isActive());
        $this->assertTrue($assessment2->isActive());
    }

    public function testSubsequentEvaluationPositiveResultMinPeriod() {
        $assessment = new Assessment(/* Provide necessary data */);
        $assessment->setEvaluationDate(new \DateTime('2022-01-01'));
        $assessmentService = new AssessmentService();

        $this->expectException(Exception::class);
        $assessmentService->conductSubsequentEvaluation($assessment, new \DateTime('2022-06-28'));
    }

    public function testSubsequentEvaluationPositiveResultAllowed() {
        $assessment = new Assessment(/* Provide necessary data */);
        $assessment->setEvaluationDate(new \DateTime('2022-01-01'));
        $assessmentService = new AssessmentService();

        $assessmentService->conductSubsequentEvaluation($assessment, new \DateTime('2022-06-29'));

        $this->assertTrue($assessment->isActive());
    }

    public function testSubsequentEvaluationNegativeResultMinPeriod() {
        $assessment = new Assessment(/* Provide necessary data */);
        $assessment->setEvaluationDate(new \DateTime('2022-01-01'));
        $assessmentService = new AssessmentService();

        $this->expectException(Exception::class);
        $assessmentService->conductSubsequentEvaluation($assessment, new \DateTime('2022-01-30'));
    }

    public function testSubsequentEvaluationNegativeResultAllowed() {
        $assessment = new Assessment(/* Provide necessary data */);
        $assessment->setEvaluationDate(new \DateTime('2022-01-01'));
        $assessmentService = new AssessmentService();

        $assessmentService->conductSubsequentEvaluation($assessment, new \DateTime('2022-01-31'));

        $this->assertTrue($assessment->isActive());
    }

}