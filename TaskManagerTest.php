<?php
declare(strict_types=1);

// ------------------------------------------------------------
//  task_manager_test.php
// ------------------------------------------------------------

use PHPUnit\Framework\TestCase;

// Adjust the path if the class definitions live elsewhere.
require_once __DIR__ . '/task_manager.php';

/**
 * Test suite for the simple in‑memory task manager.
 */
final class TaskManagerTest extends TestCase
{
    /** @var TaskCollection */
    private $collection;

    /** Set up a fresh collection before each test. */
    protected function setUp(): void
    {
        $this->collection = new TaskCollection();

        // Seed a few known tasks – this mirrors the demo data but keeps the IDs predictable.
        $this->collection->add('Buy groceries', 'Milk, eggs, bread, and cheese.');
        $this->collection->add('Finish report', 'Complete the Q3 financial report by Friday.');
    }

    /* -----------------------------------------------------------------
     *  Task class tests
     * ----------------------------------------------------------------- */

    public function testTaskConstructionAndGetters(): void
    {
        $task = new Task(42, 'Sample title', 'Sample content');

        $this->assertSame(42, $task->getId());
        $this->assertSame('Sample title', $task->getTitle());
        $this->assertSame('Sample content', $task->getContent());
    }

    public function testTaskSettersAndToString(): void
    {
        $task = new Task(1, 'Old title', 'Old content');

        $task->setTitle('New title');
        $task->setContent('New content');

        $this->assertSame('New title', $task->getTitle());
        $this->assertSame('New content', $task->getContent());

        $expected = "[1] New title\n    New content";
        $this->assertSame($expected, (string) $task);
    }

    /* -----------------------------------------------------------------
     *  TaskCollection CRUD tests
     * ----------------------------------------------------------------- */

    public function testAddCreatesTaskWithIncrementalIds(): void
    {
        $newTask = $this->collection->add('Read book', 'Start reading “Sapiens”.');

        // Two demo tasks already exist, so the next id should be 3.
        $this->assertSame(3, $newTask->getId());
        $this->assertSame('Read book', $newTask->getTitle());
        $this->assertSame('Start reading “Sapiens”.', $newTask->getContent());

        // Verify the internal storage reflects the addition.
        $this->assertCount(3, $this->collection->all());
    }

    public function testGetReturnsExistingTaskOrNull(): void
    {
        $task = $this->collection->get(1);
        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame('Buy groceries', $task->getTitle());

        $missing = $this->collection->get(999);
        $this->assertNull($missing);
    }

    public function testUpdateModifiesOnlyProvidedFields(): void
    {
        // Update only the title.
        $updated = $this->collection->update(1, 'Groceries list', null);
        $this->assertTrue($updated);
        $task = $this->collection->get(1);
        $this->assertSame('Groceries list', $task->getTitle());
        $this->assertSame('Milk, eggs, bread, and cheese.', $task->getContent());

        // Update only the content.
        $updated = $this->collection->update(2, null, 'Report due Monday.');
        $this->assertTrue($updated);
        $task = $this->collection->get(2);
        $this->assertSame('Finish report', $task->getTitle());
        $this->assertSame('Report due Monday.', $task->getContent());

        // Try to update a non‑existent task.
        $this->assertFalse($this->collection->update(999, 'X', 'Y'));
    }

    public function testDeleteRemovesTaskAndReturnsStatus(): void
    {
        $deleted = $this->collection->delete(1);
        $this->assertTrue($deleted);
        $this->assertNull($this->collection->get(1));
        $this->assertCount(1, $this->collection->all());

        // Deleting again should fail.
        $this->assertFalse($this->collection->delete(1));

        // Deleting a missing id also fails.
        $this->assertFalse($this->collection->delete(999));
    }

    public function testAllReturnsArrayOfTasksInInsertionOrder(): void
    {
        $all = $this->collection->all();
        $this->assertIsArray($all);
        $this->assertCount(2, $all);

        // Keys should correspond to the task ids.
        $this->assertArrayHasKey(1, $all);
        $this->assertArrayHasKey(2, $all);

        // Verify the objects are indeed Task instances.
        foreach ($all as $task) {
            $this->assertInstanceOf(Task::class, $task);
        }
    }
}
