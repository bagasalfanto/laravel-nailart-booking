<?php

namespace Tests\Feature\Booking;

use App\Models\Customer;
use App\Models\Nailist;
use App\Models\Reservasi;
use App\Models\StatusBooking;
use App\Models\TreatmentKatalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingStep1Test extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;

    private Nailist $nailist;

    private StatusBooking $pendingStatus;

    private TreatmentKatalog $treatment;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $customerUser = User::factory()->create();
        $customerUser->assignRole('customer');
        Customer::create(['user_id' => $customerUser->id]);
        $this->customerUser = $customerUser;

        $nailistUser = User::factory()->create();
        $this->nailist = Nailist::create(['user_id' => $nailistUser->id]);

        $this->pendingStatus = StatusBooking::create(['nama_status' => 'Pending']);

        $this->treatment = TreatmentKatalog::create([
            'nama_jasa' => 'Manicure Basic',
            'kode_jasa' => 'MNC-TEST',
            'deskripsi' => 'Test treatment',
            'price_type' => 'fixed',
            'price_min' => 75000,
            'price_max' => null,
            'durasi_menit' => 60,
            'is_active' => true,
        ]);
    }

    public function test_submit_step1_ditolak_jika_slot_bentrok(): void
    {
        $customer = Customer::where('user_id', $this->customerUser->id)->first();

        Reservasi::create([
            'customer_id' => $customer->id,
            'nailist_id' => $this->nailist->id,
            'treatment_id' => $this->treatment->id,
            'status_id' => $this->pendingStatus->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '10:00:00',
        ]);

        $response = $this->actingAs($this->customerUser)->post('/book/step1', [
            'nailist_id' => $this->nailist->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '10:30',
        ]);

        $response->assertSessionHasErrors(['jam']);
    }

    public function test_submit_step1_berhasil_jika_slot_tidak_bentrok(): void
    {
        $response = $this->actingAs($this->customerUser)->post('/book/step1', [
            'nailist_id' => $this->nailist->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '14:00',
        ]);

        $response->assertRedirect(route('booking.step2'));
        $response->assertSessionHasNoErrors();
        $this->assertEquals('14:00', session('booking_draft.step1.jam'));
    }
}
