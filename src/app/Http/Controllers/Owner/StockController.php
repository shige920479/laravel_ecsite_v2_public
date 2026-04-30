<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockCsvRequest;
use App\Http\Requests\StoreStockRequest;
use App\Models\Item;
use App\Models\StockHistory;
use App\Services\Owner\Csv\CsvExportService;
use App\Services\Owner\Csv\StockCsvDefinition;
use App\Services\Owner\StockCsvImportService;
use App\Services\Owner\StockHistoryCsvExportService;
use App\Services\Owner\StockService;
use App\Support\AppLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;


class StockController extends Controller
{
    public function index(Request $request, Item $item)
    {
        Gate::authorize('view', $item);
        $histories = $this->stockHistoryQuery($request, $item)
            ->paginate(5)->withQueryString();
        
        return view('owner.stocks.index', [
            'histories' => $histories,
            'item' => $item
        ]);
    }

    public function create(Item $item)
    {
        Gate::authorize('update', $item);
        return view('owner.stocks.create', ['item' => $item]);
    }

    public function store(StoreStockRequest $request, Item $item, StockService $stockService)
    {
        Gate::authorize('update', $item);
        try {
            $stockService->storeStockAndHistory($request->validated(), $item);

        } catch (\Throwable $e) {
            return back()->withInput()->with([
                'status' => 'alert',
                'message' => '在庫登録に失敗しました。お手数ですが、再度入力願います。'
            ]);
        }

        return to_route('owner.item.index')->with([
            'status' => 'info',
            'message' => "商品No.{$item->id}: {$item->name} の在庫登録が完了しました"
        ]);
    }

    public function downloadCsv(Request $request, Item $item ,CsvExportService $service): StreamedResponse
    {
        Gate::authorize('view', $item);

        $query = $this->stockHistoryQuery($request, $item);
        
        return $service->download($query, new StockCsvDefinition());
    }

    public function showUploadForm()
    {
        if (! Auth::user()->shop || Auth::user()->shop->items()->doesntExist()) {
            return to_route('owner.item.create')->with([
                'status' => 'alert',
                'message' => '商品が登録されていないので在庫登録ができません。先に商品登録を行ってください。'
            ]);
        }
        return view('owner.stocks.upload');
    }

    public function storeFromCsv(StoreStockCsvRequest $request, StockCsvImportService $service)
    {
        try {
            $count = $service->import($request->file('csv'), Auth::id());

            return to_route('owner.item.index')->with([
                'status' => 'info',
                'message' => "CSVファイルから {$count}件 の在庫データをアップロードしました"
            ]);

        } catch (\Throwable $e) {
            AppLog::error('csvアップロードエラー: ' ,$e);

            return back()->withErrors([
                'csv' => $e->getMessage(),
            ]);
        }
    }

    private function stockHistoryQuery(Request $request, Item $item): Builder
    {
        return
            StockHistory::forItem($item)
                ->when($request->filled('start_date'), fn ($query) =>
                    $query->fromDate($request->start_date)
                )
                ->when($request->filled('end_date'), fn ($query) =>
                    $query->toDate($request->end_date)
                )
                ->when($request->type === 'in', fn ($query) =>
                    $query->onlyIn()
                )
                ->when($request->type === 'out', fn ($query) =>
                    $query->onlyOut()
                )
                ->latest();
    }
}
