<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * E2E test for GET /api/projects/{id}?sortBy=...&direction=...
 *
 * Covers every ItemSortField.
 */
final class ProjectItemSortingTest extends WebTestCase
{
    public function testSortingByEachFieldSucceeds(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email'    => 'sort-test@example.com',
            'password' => 'password123',
            'name'     => 'Sort Tester',
        ]));

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email'    => 'sort-test@example.com',
            'password' => 'password123',
        ]));
        $this->assertResponseIsSuccessful();
        $token   = json_decode($client->getResponse()->getContent(), true)['token'];
        $headers = [
            'CONTENT_TYPE'       => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $token",
        ];

        $client->request('POST', '/api/projects', [], [], $headers, json_encode([
            'name' => 'Sort Test Project', 'description' => '',
        ]));
        $projectId = json_decode($client->getResponse()->getContent(), true)['id'];

        $client->request('POST', '/api/items', [], [], $headers, json_encode([
            'projectId' => $projectId, 'name' => 'Small', 'estimatedHours' => 1.0,
        ]));
        $client->request('POST', '/api/items', [], [], $headers, json_encode([
            'projectId' => $projectId, 'name' => 'Big', 'estimatedHours' => 20.0,
        ]));

        foreach (['name', 'estimatedHours', 'status', 'createdAt'] as $sortBy) {
            foreach (['asc', 'desc'] as $direction) {
                $client->request('GET', "/api/projects/$projectId?sortBy=$sortBy&direction=$direction", [], [], $headers);
                $this->assertResponseIsSuccessful("sortBy=$sortBy&direction=$direction should succeed");
            }
        }

        // estimatedHours ascending: Small (1h) before Big (20h)
        $client->request('GET', "/api/projects/$projectId?sortBy=estimatedHours&direction=asc", [], [], $headers);
        $items = json_decode($client->getResponse()->getContent(), true)['items'];
        $this->assertSame('Small', $items[0]['name']);
        $this->assertSame('Big', $items[1]['name']);

        // estimatedHours descending: reversed
        $client->request('GET', "/api/projects/$projectId?sortBy=estimatedHours&direction=desc", [], [], $headers);
        $items = json_decode($client->getResponse()->getContent(), true)['items'];
        $this->assertSame('Big', $items[0]['name']);
        $this->assertSame('Small', $items[1]['name']);

        $client->request('DELETE', "/api/projects/$projectId", [], [], $headers);
        $this->assertResponseStatusCodeSame(204);
    }
}
