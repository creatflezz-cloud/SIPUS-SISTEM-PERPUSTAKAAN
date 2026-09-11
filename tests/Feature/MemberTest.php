<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsPetugas(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_tambah_anggota_data_valid(): void
    {
        $this->actingAsPetugas()
            ->post(route('members.store'), [
                'member_code' => 'MBR-1001',
                'name' => 'Budi',
                'gender' => 'L',
                'phone' => '08123456789',
                'address' => 'Jl. Test 1',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('members.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('members', ['member_code' => 'MBR-1001']);
    }

    public function test_tambah_anggota_nomor_kosong_gagal(): void
    {
        $this->actingAsPetugas()
            ->post(route('members.store'), [
                'member_code' => '',
                'name' => 'Budi',
                'gender' => 'L',
                'status' => 'aktif',
            ])
            ->assertSessionHasErrors('member_code');

        $this->assertDatabaseMissing('members', ['name' => 'Budi']);
    }

    public function test_tambah_anggota_nama_kosong_gagal(): void
    {
        $this->actingAsPetugas()
            ->post(route('members.store'), [
                'member_code' => 'MBR-1002',
                'name' => '',
                'gender' => 'L',
                'status' => 'aktif',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_tambah_anggota_nomor_duplikat_gagal(): void
    {
        Member::create([
            'member_code' => 'MBR-1003',
            'name' => 'Anggota Lama',
            'gender' => 'L',
            'status' => 'aktif',
        ]);

        $this->actingAsPetugas()
            ->post(route('members.store'), [
                'member_code' => 'MBR-1003',
                'name' => 'Anggota Baru',
                'gender' => 'P',
                'status' => 'aktif',
            ])
            ->assertSessionHasErrors('member_code');

        $this->assertDatabaseMissing('members', ['name' => 'Anggota Baru']);
    }

    public function test_edit_anggota(): void
    {
        $member = Member::create([
            'member_code' => 'MBR-1004',
            'name' => 'Anggota Awal',
            'gender' => 'L',
            'status' => 'aktif',
        ]);

        $this->actingAsPetugas()
            ->put(route('members.update', $member), [
                'member_code' => 'MBR-1004',
                'name' => 'Anggota Update',
                'gender' => 'L',
                'phone' => '081200000',
                'address' => 'Jl. Baru',
                'status' => 'tidak_aktif',
            ])
            ->assertRedirect(route('members.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Anggota Update',
            'status' => 'tidak_aktif',
        ]);
    }

    public function test_hapus_anggota(): void
    {
        $member = Member::create([
            'member_code' => 'MBR-1005',
            'name' => 'Anggota Dihapus',
            'gender' => 'P',
            'status' => 'aktif',
        ]);

        $this->actingAsPetugas()
            ->delete(route('members.destroy', $member))
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    }

    public function test_cari_anggota(): void
    {
        Member::create(['member_code' => 'MBR-A01', 'name' => 'Cari Saya', 'gender' => 'L', 'status' => 'aktif']);
        Member::create(['member_code' => 'MBR-B02', 'name' => 'Jangan Muncul', 'gender' => 'L', 'status' => 'aktif']);

        $this->actingAsPetugas()
            ->get(route('members.index', ['search' => 'Cari Saya']))
            ->assertOk()
            ->assertSee('Cari Saya')
            ->assertDontSee('Jangan Muncul');
    }

    public function test_filter_status_anggota(): void
    {
        Member::create(['member_code' => 'MBR-C03', 'name' => 'Aktif Saja', 'gender' => 'L', 'status' => 'aktif']);
        Member::create(['member_code' => 'MBR-D04', 'name' => 'NonAktif Saja', 'gender' => 'L', 'status' => 'tidak_aktif']);

        $this->actingAsPetugas()
            ->get(route('members.index', ['status' => 'aktif']))
            ->assertOk()
            ->assertSee('Aktif Saja')
            ->assertDontSee('NonAktif Saja');
    }
}