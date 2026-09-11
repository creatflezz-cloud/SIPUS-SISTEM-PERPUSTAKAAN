<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), [Member::STATUS_AKTIF, Member::STATUS_TIDAK_AKTIF], true)) {
            $query->where('status', $request->input('status'));
        }

        $members = $query->latest()->paginate(10);

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        return view('members.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_code' => ['required', 'string', 'max:20', 'unique:members,member_code'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:aktif,tidak_aktif'],
        ], [
            'member_code.required' => 'Nomor anggota wajib diisi.',
            'member_code.unique' => 'Nomor anggota sudah digunakan.',
            'name.required' => 'Nama wajib diisi.',
        ]);

        Member::create($data);

        return redirect()->route('members.index')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'member_code' => ['required', 'string', 'max:20', 'unique:members,member_code,'.$member->id],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:aktif,tidak_aktif'],
        ], [
            'member_code.required' => 'Nomor anggota wajib diisi.',
            'member_code.unique' => 'Nomor anggota sudah digunakan.',
            'name.required' => 'Nama wajib diisi.',
        ]);

        $member->update($data);

        return redirect()->route('members.index')->with('success', 'Anggota berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Anggota berhasil dihapus.');
    }
}