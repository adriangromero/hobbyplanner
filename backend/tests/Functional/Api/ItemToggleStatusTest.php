<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * E2E test for the Item toggle-status API endpoint.
 *
 * Prerequisites:
 *   - A test database with the migration applied
 *   - A seeded user (test@example.com / password123)
 *
 * Run:
 *   php bin/phpunit --filter=ItemToggleStatusTest
 */
final class ItemToggleStatusTest extends WebTestCase
{
    /**
     * Tests the full status lifecycle for an item:
     *   1. POST /api/auth/login  → get JWT
     *   2. POST /api/items       → create an item (starts pending)
     *   3. PUT  /api/items/{id}/toggle-status → completed
     *   4. GET  /api/projects/{id}/estimation → verify estimation reflects the item's hours
     *   5. PUT  /api/items/{id}/toggle-status → back to pending
     */
    public function testTogglingItemStatusFlipsStatusAndEstimation(): void
    {
        $client = static::createClient();

        // 1. Register (idempotent — ignore "already exists") then login
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email'    => 'test@example.com',
            'password' => 'password123',
            'name'     => 'E2E Tester',
        ]));

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]));

        $this->assertResponseIsSuccessful();
        $loginData = json_decode($client->getResponse()->getContent(), true);
        $token     = $loginData['token'];
        $headers   = [
            'CONTENT_TYPE'       => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $token",
        ];

        // 2. Create item (starts as 'pending')
        $projectId = $this->getTestProjectId($client, $headers);

        $client->request('POST', '/api/items', [], [], $headers, json_encode([
            'name'           => 'E2E Item',
            'estimatedHours' => 2.0,
            'projectId'      => $projectId,
        ]));

        $this->assertResponseStatusCodeSame(201);
        $itemData = json_decode($client->getResponse()->getContent(), true);
        $itemId   = $itemData['id'];
        $this->assertSame('pending', $itemData['status']);

        // 3. Toggle → completed
        $client->request('PUT', "/api/items/$itemId/toggle-status", [], [], $headers);
        $this->assertResponseIsSuccessful();
        $toggleData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('completed', $toggleData['status']);

        // 4. Estimation reflects the item's hours
        $client->request('GET', "/api/projects/$projectId/estimation", [], [], $headers);
        $this->assertResponseIsSuccessful();
        $estimation = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(2.0, $estimation['estimatedHours']);

        // 5. Toggle → back to pending
        $client->request('PUT', "/api/items/$itemId/toggle-status", [], [], $headers);
        $this->assertResponseIsSuccessful();
        $toggleData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('pending', $toggleData['status']);

        // 6. Cleanup
        $client->request('DELETE', "/api/items/$itemId", [], [], $headers);
        $this->assertResponseStatusCodeSame(204);
    }

    private function getTestProjectId($client, array $headers): string
    {
        $client->request('GET', '/api/projects', [], [], $headers);
        $data = json_decode($client->getResponse()->getContent(), true);

        if (!empty($data['projects'])) {
            return $data['projects'][0]['id'];
        }

        // Create one if none exists
        $client->request('POST', '/api/projects', [], [], $headers, json_encode([
            'name'        => 'E2E Test Project',
            'description' => 'Created by E2E test',
        ]));

        $projectData = json_decode($client->getResponse()->getContent(), true);
        return $projectData['id'];
    }
}
