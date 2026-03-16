<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there is at least one user for author_id
        $admin = User::first() ?? User::factory()->create();

        // 6 Dimensions (levels of hierarchy) with 2 branches each creates approx 126 structured records.
        $maxDimension = 6;
        $branches = 2;

        $this->seedDimension($admin->id, null, 1, $maxDimension, $branches);
    }

    /**
     * Recursively populates the hierarchy dimensions.
     */
    private function seedDimension(int $authorId, ?int $parentId, int $currentDimension, int $maxDimension, int $branches): void
    {
        if ($currentDimension > $maxDimension) {
            return;
        }

        for ($i = 0; $i < $branches; $i++) {
            $node = Department::factory()->create([
                'author_id' => $authorId,
                'parent_id' => $parentId,
            ]);

            $this->seedDimension($authorId, $node->id, $currentDimension + 1, $maxDimension, $branches);
        }
    }
}
