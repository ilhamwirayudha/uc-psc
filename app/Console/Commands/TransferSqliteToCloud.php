<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferSqliteToCloud extends Command
{
    protected $signature = 'db:transfer-sqlite';
    protected $description = 'Transfer all records from local database.sqlite to current cloud database';

    public function handle(): int
    {
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error("File database.sqlite tidak ditemukan di {$sqlitePath}");
            return 1;
        }

        config(['database.connections.sqlite.database' => $sqlitePath]);

        $this->info("Memulai transfer data dari SQLite lokal ke Supabase PostgreSQL...");

        // 1. Users, Counselors, Clients, Participants
        $simpleTables = [
            'users',
            'counselors',
            'clients',
            'client_participants',
            'client_staff_assignments',
            'payment_transactions',
        ];

        foreach ($simpleTables as $table) {
            $this->copyTable($table);
        }

        // 2. Client Forms & Bookings have circular foreign keys:
        // bookings -> client_form_id (points to client_forms)
        // client_forms -> booking_id (points to bookings)
        // Solusi: Insert client_forms dulu dengan booking_id = null, insert bookings, lalu update client_forms.booking_id!
        $this->info("Mentransfer berkas formulir dan booking jadwal...");

        DB::table('client_forms')->delete();
        DB::table('bookings')->delete();

        $clientForms = DB::connection('sqlite')->table('client_forms')->get();
        $bookings = DB::connection('sqlite')->table('bookings')->get();

        // Step A: Insert client_forms dengan booking_id = null
        foreach ($clientForms as $f) {
            $data = (array) $f;
            $data['booking_id'] = null;
            DB::table('client_forms')->insert($data);
        }

        // Step B: Insert bookings
        foreach ($bookings as $b) {
            $data = (array) $b;
            DB::table('bookings')->insert($data);
        }

        // Step C: Update booking_id di client_forms sesuai data asli
        foreach ($clientForms as $f) {
            if ($f->booking_id) {
                DB::table('client_forms')->where('id', $f->id)->update(['booking_id' => $f->booking_id]);
            }
        }

        $this->resetSequence('client_forms');
        $this->resetSequence('bookings');
        $this->info("  ✔ client_forms: " . count($clientForms) . " baris berhasil ditransfer");
        $this->info("  ✔ bookings: " . count($bookings) . " baris berhasil ditransfer");

        // 3. Booking participants & Pairings
        $this->copyTable('booking_participants');
        $this->copyTable('pairings');

        $this->newLine();
        $this->info("Transfer data ke Supabase SELESAI dengan sukses!");
        return 0;
    }

    private function copyTable(string $table): void
    {
        if (!Schema::connection('sqlite')->hasTable($table)) {
            return;
        }

        $records = DB::connection('sqlite')->table($table)->get();
        $count = $records->count();

        if ($count === 0) {
            $this->line("  - {$table}: 0 baris (dilewati)");
            return;
        }

        DB::table($table)->delete();

        foreach ($records->chunk(50) as $chunk) {
            $data = $chunk->map(function ($item) {
                return (array) $item;
            })->toArray();

            DB::table($table)->insert($data);
        }

        $this->resetSequence($table);
        $this->info("  ✔ {$table}: {$count} baris berhasil ditransfer");
    }

    private function resetSequence(string $table): void
    {
        try {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), coalesce(max(id), 1)) FROM {$table};");
        } catch (\Throwable $e) {
            // Abaikan jika tabel tidak memiliki auto-increment sequence serial
        }
    }
}
