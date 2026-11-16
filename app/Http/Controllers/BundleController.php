<?php

namespace App\Http\Controllers;

use App\Http\Requests\BundleRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\BundleDraft;
use App\Services\BundleRecommender;
use App\Services\CartService;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function __construct(
        private BundleRecommender $recommender,
        private BundleDraft $draft,
        private CartService $cart,
    ) {}

    /** Form kebutuhan. */
    public function index(Request $request)
    {
        return view('bundle.index', [
            'prefill' => $request->only(['need', 'people', 'budget', 'meals']),
        ]);
    }

    /** Hasil rekomendasi. */
    public function result(BundleRequest $request)
    {
        $params = $request->params();

        return view('bundle.result', [
            'params' => $params,
            'bundles' => $this->recommender->recommend(...$params)->values(),
            'need' => config("frozemate.needs.{$params['need']}"),
            'budget' => config("frozemate.budgets.{$params['budget']}"),
        ]);
    }

    /** Pilih salah satu rekomendasi untuk diracik ulang. */
    public function choose(BundleRequest $request)
    {
        $bundle = $this->pick($request);

        if (! $bundle) {
            return redirect()->route('bundle.index')->with('error', 'Rekomendasi tidak ditemukan. Coba isi ulang kebutuhanmu.');
        }

        $this->draft->start(
            $bundle['name'],
            $request->params(),
            $bundle['items']->mapWithKeys(fn ($item) => [$item['product']->id => $item['qty']])->all(),
        );

        return redirect()->route('bundle.customize');
    }

    /** Masukkan rekomendasi langsung ke keranjang tanpa diracik. */
    public function addRecommended(BundleRequest $request)
    {
        $bundle = $this->pick($request);

        if (! $bundle) {
            return redirect()->route('bundle.index')->with('error', 'Rekomendasi tidak ditemukan. Coba isi ulang kebutuhanmu.');
        }

        $result = $this->cart->addBundle(
            $bundle['name'],
            $bundle['items']->mapWithKeys(fn ($item) => [$item['product']->id => $item['qty']])->all(),
        );

        return redirect()->route('cart.index')->with(...$this->cartFlash($bundle['name'], $result));
    }

    /** Halaman racik bundle. */
    public function customize()
    {
        return view('bundle.customize', [
            'summary' => $this->draft->summary(),
            'products' => Product::with('category')->available()->orderBy('category_id')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'fromRecommendation' => $this->draft->exists(),
        ]);
    }

    /** Simpan perubahan isi bundle, kembalikan total terbaru (JSON). */
    public function update(Request $request)
    {
        $data = $request->validate([
            'items' => ['present', 'array', 'max:30'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:'.BundleDraft::MAX_QTY],
        ]);

        $this->draft->setItems(
            collect($data['items'])->mapWithKeys(fn ($item) => [(int) $item['id'] => (int) $item['qty']])->all()
        );

        return response()->json($this->draft->summary());
    }

    /** Masukkan bundle racikan ke keranjang. */
    public function addCustomized()
    {
        $draft = $this->draft->get();

        if (empty($draft['items'])) {
            return back()->with('error', 'Bundle masih kosong. Tambahkan minimal satu produk.');
        }

        $result = $this->cart->addBundle($draft['name'], $draft['items']);
        $this->draft->forget();

        return redirect()->route('cart.index')->with(...$this->cartFlash($draft['name'], $result));
    }

    private function pick(BundleRequest $request): ?array
    {
        $variant = $request->validate(['variant' => ['required', 'in:pas,hemat,variatif']])['variant'];

        return $this->recommender->recommend(...$request->params())->get($variant);
    }

    private function cartFlash(string $name, array $result): array
    {
        if ($result['added'] === 0) {
            return ['error', 'Stok produk di bundle ini sedang habis.'];
        }

        if ($result['short']) {
            return ['info', "$name masuk keranjang, tapi stok kurang untuk: ".implode(', ', $result['short']).'.'];
        }

        return ['success', "$name masuk keranjang."];
    }
}
