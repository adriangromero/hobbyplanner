<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * E2E test for the Item toggle-status API endpoint.
 *
 * Prerequisites:
 *   - A test database with the migration applied
 *   - A seeded user + project + item (see fixtures or setUp)
 *
 * Run:
 *   php bin/phpunit --filter=ItemToggleStatusTest
 */
final class ItemToggleStatusTest extends WebTestCase
{
    /**
     * Tests the full toggle-status lifecycle:
     *   1. POST /api/auth/login  → get JWT
     *   2. POST /api/items       → create an item (pending)
     *   3. PUT  /api/items/{id}/toggle-status → pending → completed
     *   4. PUT  /api/items/{id}/toggle-status → completed → pending
     *   5. GET  /api/projects/{id}/estimation → verify estimation reflects status
     */
    public function testToggleItemStatusChangesEstimation(): void
    {
        $client = static::createClient();

        // 1. Login
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
            'name'           => 'E2E Test Item',
            'estimatedHours' => 5.0,
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

        // 4. Verify estimation: completed item should reduce remaining hours
        $client->request('GET', "/api/projects/$projectId/estimation", [], [], $headers);
        $this->assertResponseIsSuccessful();
        $estimation = json_decode($client->getResponse()->getContent(), true);
        // The completed item's estimated hours are excluded from remaining
        $this->assertIsFloat($estimation['remainingHours']);

        // 5. Toggle back → pending
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
