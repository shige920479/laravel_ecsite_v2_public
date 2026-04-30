<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewHelpufulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            $reviews = Review::select('id', 'user_id')->get();
            $userIds = User::select('id')->get();
            $date = now();
            $insertData = [];

            foreach ($reviews as $review) {

                $selectedIds = $userIds
                    ->reject(fn ($id) => $id === $review->user_id)
                    ->shuffle()
                    ->take(rand(0,25))
                    ->pluck('id');

                if ($selectedIds->isEmpty()) continue;

                foreach ($selectedIds as $userId) {
                    $insertData[] = [
                        'user_id' => $userId,
                        'review_id' => $review->id,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }

                if (count($insertData) > 100) {
                    DB::table('review_helpfuls')->insert($insertData);
                    $insertData = [];
                }
            }

            if (! empty($insertData)) {
                DB::table('review_helpfuls')->insert($insertData);
            }
        });
    }
}
