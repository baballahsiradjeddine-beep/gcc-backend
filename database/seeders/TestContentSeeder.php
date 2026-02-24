<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Material;
use App\Models\Unit;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestContentSeeder extends Seeder
{
    public function run()
    {
        $division = Division::firstOrCreate(
            ['name' => 'شعبة التجربة (Test Division)'],
            ['description' => 'شعبة مخصصة لتجربة مميزات التطبيق']
        );

        $material = Material::firstOrCreate(
            ['code' => 'MAGIC_101'],
            ['name' => 'مادة التجربة السحرية', 'description' => 'مادة لاختبار الشعلة والنقاط', 'color' => '#FF0000', 'active' => true]
        );

        // Attach the material to the division correctly
        $division->materials()->syncWithoutDetaching([$material->id]);

        $unit = Unit::firstOrCreate(
            ['name' => 'الوحدة الأولى: البداية'],
            ['description' => 'وحدة لجمع النقاط', 'active' => true]
        );

        $material->units()->syncWithoutDetaching([$unit->id => ['sort' => 1]]);

        $chapter = Chapter::firstOrCreate(
            ['name' => 'الفصل الأول: تحدي الشعلة'],
            ['description' => 'فصل يحتوي على 4 أسئلة تجريبية', 'active' => true]
        );

        $unit->chapters()->syncWithoutDetaching([$chapter->id => ['sort' => 1]]);

        // Attach guest subscription to unit and chapter so it is visible
        $guestSub = Subscription::where('id', Subscription::GUEST_ID)->first();
        if ($guestSub) {
            $unit->subscriptions()->syncWithoutDetaching([$guestSub->id]);
            $chapter->subscriptions()->syncWithoutDetaching([$guestSub->id]);
        }

        // Questions have many to many with chapters
        // Ensure no previous ones exist in this chapter
        $existingQIds = DB::table('chapter_question')->where('chapter_id', $chapter->id)->pluck('question_id');
        if ($existingQIds->isNotEmpty()) {
            Question::whereIn('id', $existingQIds)->delete();
            DB::table('chapter_question')->where('chapter_id', $chapter->id)->delete();
        }

        // Question 1
        $q1 = Question::create([
            'question' => 'ما هو لون السماء في يوم صافٍ؟',
            'question_type' => 'multiple_choices',
            'options' => [
                ['id' => '1', 'text' => 'أزرق', 'is_correct' => true],
                ['id' => '2', 'text' => 'أحمر', 'is_correct' => false],
                ['id' => '3', 'text' => 'أخضر', 'is_correct' => false],
                ['id' => '4', 'text' => 'أصفر', 'is_correct' => false],
            ],
            'scope' => 'exercice'
        ]);
        DB::table('chapter_question')->insert(['chapter_id' => $chapter->id, 'question_id' => $q1->id, 'sort' => 1]);

        // Question 2
        $q2 = Question::create([
            'question' => 'كم عدد أيام الأسبوع؟',
            'question_type' => 'multiple_choices',
            'options' => [
                ['id' => '1', 'text' => '5', 'is_correct' => false],
                ['id' => '2', 'text' => '6', 'is_correct' => false],
                ['id' => '3', 'text' => '7', 'is_correct' => true],
                ['id' => '4', 'text' => '8', 'is_correct' => false],
            ],
            'scope' => 'exercice'
        ]);
        DB::table('chapter_question')->insert(['chapter_id' => $chapter->id, 'question_id' => $q2->id, 'sort' => 2]);

        // Question 3
        $q3 = Question::create([
            'question' => 'أي من هذه الكلمات تعني (كتاب) باللغة الإنجليزية؟',
            'question_type' => 'multiple_choices',
            'options' => [
                ['id' => '1', 'text' => 'Pen', 'is_correct' => false],
                ['id' => '2', 'text' => 'Book', 'is_correct' => true],
                ['id' => '3', 'text' => 'Door', 'is_correct' => false],
                ['id' => '4', 'text' => 'Window', 'is_correct' => false],
            ],
            'scope' => 'exercice'
        ]);
        DB::table('chapter_question')->insert(['chapter_id' => $chapter->id, 'question_id' => $q3->id, 'sort' => 3]);


        // Question 4
        $q4 = Question::create([
            'question' => 'ما هو ناتج ضرب 5 * 5؟',
            'question_type' => 'multiple_choices',
            'options' => [
                ['id' => '1', 'text' => '10', 'is_correct' => false],
                ['id' => '2', 'text' => '20', 'is_correct' => false],
                ['id' => '3', 'text' => '25', 'is_correct' => true],
                ['id' => '4', 'text' => '50', 'is_correct' => false],
            ],
            'scope' => 'exercice'
        ]);
        DB::table('chapter_question')->insert(['chapter_id' => $chapter->id, 'question_id' => $q4->id, 'sort' => 4]);

        
        // Ensure user belongs to this division
        $user = User::first();
        if ($user && empty($user->division_id)) {
            $user->division_id = $division->id;
            $user->save();
        }

        echo "Done building test division, material, unit, chapter, and 4 questions! \n";
    }
}
