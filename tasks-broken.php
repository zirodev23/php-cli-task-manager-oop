<?php
/**
 * task_manager.php
 *
 * CLI CRUD task manager (in‑memory only).
 * - Uses readline() for input.
 * - Starts with a handful of demo tasks.
 *
 * Run with: php task_manager.php
 */

/* ==============================
   Model classes
   ============================== */
class Task
{
    private int $id;
    private string $title;
    private string $content;

    public function __construct(int $id, string $title, string $content)
    {
        $this->id      = $id;
        $this->title   = $title;
        $this->content = $content;
    }

    // ----- getters / setters -----
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getContent(): string { return $this->content; }

    public function setTitle(string $title): void   { $this->title = $title; }
    public function setContent(string $content): void { $this->content = $content; }

    public function __toString(): string
    {
        return sprintf("[%d] %s\n    %s", $this->id, $this->title, $this->content);
    }
}

class TaskCollection
{
    /** @var Task[] */
    private array $tasks = [];
    private int $nextId = 1;

    // CREATE
    public function add(string $title, string $content): Task
    {
        $task = new Task($this->nextId++, $title, $content);
        $this->tasks[$task->getId()] = $task;
        return $task;
    }

    // READ – single
    public function get(int $id): ?Task
    {
        return $this->tasks[$id] ?? null;
    }

    // READ – all
    public function all(): array
    {
        return $this->tasks;
    }

    // UPDATE
    public function update(int $id, ?string $title = null, ?string $content = null): bool
    {
        $task = $this->get($id);
        if (!$task) {
            return false;
        }
        if ($title !== null)  $task->setTitle($title);
        if ($content !== null) $task->setContent($content);
        return true;
    }

    // DELETE
    public function delete(int $id): bool
    {
        if (!isset($this->tasks[$id])) {
            return false;
        }
        unset($this->tasks[$id]);
        return true;
    }
}

/* ==============================
   Helper functions (readline)
   ============================== */
function rl(string $msg): string
{
    $input = readline($msg . ': ');
    // Store the line in history so the user can press ↑ later
    if ($input !== false) {
        readline_add_history($input);
    }
    return trim((string)$input);
}

function printMenu(): void
{
    echo "\n=== Task Manager (CLI) ===\n";
    echo "1) List all tasks\n";
    echo "2) Create a new task\n";
    echo "3) View a task\n";
    echo "4) Update a task\n";
    echo "5) Delete a task\n";
    echo "6) Exit\n";
}

/* ==============================
   Initialise with demo data
   ============================== */
$collection = new TaskCollection();

// Sample tasks – feel free to edit or add more
$demoData = [
    ['Buy groceries',       'Milk, eggs, bread, and cheese.'],
    ['Finish report',       'Complete the Q3 financial report by Friday.'],
    ['Call Mom',            'Check in and see how she’s doing.'],
    ['Read book',           'Start reading “Sapiens” – chapters 1‑3.'],
    ['Plan weekend trip',   'Research cabins near the lake for Saturday night.']
];

foreach ($demoData as [$title, $content]) {
    $collection->add($title, $content);
}

/* ==============================
   Main interactive loop
   ============================== */
while (true) {
    printMenu();
    $choice = rl('Select an option');

    switch ($choice) {
        case '1': // List all
            $tasks = $collection->all();
            if (empty($tasks)) {
                echo "No tasks found.\n";
            } else {
                foreach ($tasks as $t) {
                    echo $t . "\n";
                }
            }
            break;

        case '2': // Create
            $title   = rl('Enter title');
            $content = rl('Enter content');
            $task    = $collection->add($title, $content);
            echo "Created task #{$task->getId()}.\n";
            break;

        case '3': // View single
            $id   = (int)rl('Enter task ID');
            $task = $collection->get($id);
            echo $task ? $task . "\n" : "Task #{$id} not found.\n";
            break;

        case '4': // Update
            $id = (int)rl('Enter task ID to update');
            $task = $collection->get($id);
            if (!$task) {
                echo "Task #{$id} not found.\n";
                break;
            }
            $newTitle   = rl('New title (blank to keep)');
            $newContent = rl('New content (blank to keep)');

            $updated = $collection->update(
                $id,
                $newTitle !== '' ? $newTitle : null,
                $newContent !== '' ? $newContent : null
            );
            echo $updated ? "Task #{$id} updated.\n" : "Failed to update task.\n";
            break;

        case '5': // Delete
            $id = (int)rl('Enter task ID to delete');
            $deleted = $collection->delete($id);
            echo $deleted ? "Task #{$id} deleted.\n" : "Task #{$id} not found.\n";
            break;

        case '6': // Exit
            echo "Goodbye!\n";
            exit(0);

        default:
            echo "Please choose a number between 1‑6.\n";
    }
}