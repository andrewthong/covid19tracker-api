<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notes = [
            // title, description, expiry_date, tag, priority, type
            ['Sample Title', 'Test A the quick brown fox jumps over the lazy dog.', '2020-09-15', 'demo', 50, 'warning'],
            ['Expired Title', 'Test B', '2020-08-15', null, null, 'info'],
            ['', 'Test C no title', '2020-10-15', 'demo', null, 'info'],
            ['High Priority', 'Test D', '2020-12-31', null, 10, 'danger'],
            ['', 'Test E always active', null, null, null, 'info'],
            ['', 'Test F No Description', '2020-09-20', 'demo', null, 'success'],
            ['Assert IGH 2', 'Test G', '2020-10-10', 'demo', null, 'success'],
            ['Assert IGH 3', 'Test H', '2020-10-15', 'demo', null, 'success'],
            ['Assert IGH 1', 'Test I', '2020-10-05', 'demo', null, 'success'],
            ['Even Higher Priority', 'Test I', '2021-01-01', null, 9, 'success'],
        ];
        foreach ($notes as $note) {
            DB::table('notes')->insert([
                'title' => $note[0],
                'description' => $note[1],
                'expiry_date' => $note[2],
                'tag' => $note[3],
                'priority' => $note[4],
                'type' => $note[5],
            ]);
        }
    }
}
