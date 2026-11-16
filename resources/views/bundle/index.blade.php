@extends('layouts.app')

@section('title', 'Smart Bundle')

@section('content')
    <div class="container">
        <div class="row g-5 align-items-start" style="padding-top:clamp(2rem,5vw,3.5rem)">
            <div class="col-lg-5">
                <span class="eyebrow">Smart Bundle</span>
                <h1 class="mt-2">Empat jawaban, satu bundel yang pas.</h1>
                <p class="muted" style="font-size:1.05rem;max-width:44ch">
                    Isi panel di samping. Kami hitung porsinya, cek stok, lalu susun kombinasi yang tidak melewati budgetmu.
                    Hasilnya masih bisa kamu ubah.
                </p>

                <div class="card-fm mt-4">
                    <div class="card-fm__head"><h4>Aturan mainnya</h4><span class="tag">tanpa AI, tanpa rahasia</span></div>
                    <div class="card-fm__body">
                        <ul class="rule-list rule-list--light">
                            <li><i class="ph-fill ph-check-circle"></i><span><strong>Target porsi</strong> = jumlah orang × jumlah kali makan.</span></li>
                            <li><i class="ph-fill ph-check-circle"></i><span>Produk yang <strong>cocok dengan kebutuhanmu</strong> diutamakan.</span></li>
                            <li><i class="ph-fill ph-check-circle"></i><span>Total <strong>tidak pernah lewat</strong> batas atas budget.</span></li>
                            <li><i class="ph-fill ph-check-circle"></i><span>Camilan dihitung setengah porsi supaya bundel tetap berisi lauk.</span></li>
                            <li><i class="ph-fill ph-check-circle"></i><span>Kamu dapat sampai <strong>tiga pilihan</strong>: pas, hemat, dan variasi.</span></li>
                        </ul>
                    </div>
                </div>

                <a class="link-arrow mt-4" href="{{ route('bundle.customize') }}">
                    Mau racik sendiri dari nol? <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            <div class="col-lg-7">
                <x-bundle-form id="page" :prefill="$prefill" />
            </div>
        </div>
    </div>
@endsection
