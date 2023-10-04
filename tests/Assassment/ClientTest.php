<?php

namespace Tests\Assessment;

use Domain\Assessment\Service\AssessmentService;
use PHPUnit\Framework\TestCase;
use Domain\Assessment\Model\Client;
use Domain\Assessment\Model\Assessment;
use Domain\Assessment\Model\Standard;

class ClientTest extends TestCase {
    public function testClientCanHaveMultipleAssessmentsInDifferentStandards() {
        // Arrange
        $client = new Client([/* Initialize client data */]);
        $assessmentService = new AssessmentService();
        $standard1 = new Standard([/* Initialize standard data 1 */]);
        $standard2 = new Standard([/* Initialize standard data 2 */]);

        // Act
        $assessment1 = $assessmentService->createAssessmentInStandard([/* Provide necessary data for assessment 1 */], $standard1);
        $assessment2 = $assessmentService->createAssessmentInStandard([/* Provide necessary data for assessment 2 */], $standard2);

        $client->addAssessment($assessment1);
        $client->addAssessment($assessment2);

        // Assert
        $this->assertCount(2, $client->getAssessments());
    }
}