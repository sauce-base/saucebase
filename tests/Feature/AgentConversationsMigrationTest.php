<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Migrations\AiMigration;
use Tests\TestCase;

class AgentConversationsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_migration_manages_the_complete_conversation_schema(): void
    {
        $migration = require database_path('migrations/0001_01_01_000007_create_agent_conversations_table.php');

        $this->assertInstanceOf(AiMigration::class, $migration);

        $migration->down();

        try {
            $migration->up();

            $this->assertTrue(Schema::hasTable('agent_conversations'));
            $this->assertTrue(Schema::hasTable('agent_conversation_messages'));
            $this->assertTrue($this->columnIsNullable('agent_conversations', 'user_id'));
            $this->assertTrue($this->columnIsNullable('agent_conversation_messages', 'user_id'));

            $this->assertTrue(Schema::hasIndex('agent_conversations', ['id'], 'primary'));
            $this->assertTrue(Schema::hasIndex('agent_conversation_messages', ['id'], 'primary'));
            $this->assertTrue(Schema::hasIndex('agent_conversations', ['user_id', 'updated_at']));
            $this->assertTrue(Schema::hasIndex('agent_conversation_messages', ['conversation_id']));
            $this->assertTrue(Schema::hasIndex('agent_conversation_messages', 'conversation_index'));
            $this->assertTrue(Schema::hasIndex('agent_conversation_messages', ['user_id']));

            $migration->down();

            $this->assertFalse(Schema::hasTable('agent_conversations'));
            $this->assertFalse(Schema::hasTable('agent_conversation_messages'));
        } finally {
            if (! Schema::hasTable('agent_conversations') || ! Schema::hasTable('agent_conversation_messages')) {
                $migration->down();
                $migration->up();
            }
        }
    }

    private function columnIsNullable(string $table, string $column): bool
    {
        $columnDefinition = collect(Schema::getColumns($table))->firstWhere('name', $column);

        return $columnDefinition['nullable'] ?? false;
    }
}
