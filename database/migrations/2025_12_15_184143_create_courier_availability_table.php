<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('city_id');
            $table->string('courier');
            $table->boolean('available')->default(false);
            $table->timestamps();

            $table->unique(['province_id', 'city_id', 'courier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_availability');
    }
};
