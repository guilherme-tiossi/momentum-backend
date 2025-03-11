<?php

namespace App\Services;

use Laudis\Neo4j\ClientBuilder;

class Neo4jService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://neo4j:tiossi13y@localhost')
            ->build();
    }

    public function addInterests($user, $interests)
    {
        foreach ($interests as $interest) {
            $this->client->run(<<<'CYPHER'
            MERGE (u:User {mysql_id: $userId})
            ON CREATE SET u.name = $userName
            MERGE (i:Interest {name: $interest})
            MERGE (u)-[r:HAS_INTEREST]->(i)
            ON CREATE SET r.weight = 1
            ON MATCH SET r.weight = r.weight + 1
        CYPHER, [
                'userId' => $user->id,
                'userName' => $user->name,
                'interest' => $interest,
            ]);
        }
    }
}
