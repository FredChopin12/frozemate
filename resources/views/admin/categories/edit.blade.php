@extends('layouts.admin')

@section('title', 'Edit '.$category->name)
@section('eyebrow', 'Katalog / Kategori')
@section('heading', 'Edit kategori')

@section('actions')
    <a class="btn-fm btn-fm--ghost" href="{{ route('admin.categories.index') }}"><i class="ph-bold ph-arrow-left"></i> Kembali</a>
@endsection

@section('content')
    <div class="card-fm" style="max-width:560px">
        <div class="card-fm__body">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" novalidate>
                @csrf @method('PUT')
                @include('admin.categories._form')
                <button class="btn-fm btn-fm--primary" type="submit"><i class="ph-bold ph-floppy-disk"></i> Simpan perubahan</button>
            </form>
        </div>
    </div>
@endsection
