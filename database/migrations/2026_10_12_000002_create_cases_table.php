<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();                                  // auto-increment ID
            $table->string('case_number')->unique();       // unique index
            $table->string('patient_reference');           // dummy code, not a real name
            $table->string('surgeon_name');
            $table->string('implant_type');
            $table->string('status')->index();             // index for faster filtering
            $table->string('priority');
            $table->date('surgery_date')->nullable();      // optional
            $table->foreignId('created_by')->constrained('users'); // foreign key to users.id
            $table->timestamps();                          // created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
