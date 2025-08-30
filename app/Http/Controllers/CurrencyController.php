<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    // GET /currencies
    public function index()
    {
        $currencies=Currency::all();
        return view('backend.currency.index', compact('currencies'));
    }
   
    // POST /currencies
    public function store(Request $request)
    {
     
        $currency = Currency::create($request->all());

        return back()->with('message', 'تم الاضافة بنجاح');
    }

    // PUT/PATCH /currencies/{currency}
    public function update(Request $request,  $id)
    {
        $curr = Currency::findOrFail($id);
        $input = $request->all();
       
        $curr->update($input);
        
        return back()->with('message', 'تم التعديل بنجاح');
    }



    // GET /currencies/rate/{code}
    public function getRate($code)
    {
        $currency = Currency::where('code', $code)->firstOrFail();
        return response()->json([
            'code' => $currency->code,
            'rate' => $currency->rate,
            'base' => Currency::getBaseCurrency()->code
        ]);
    }

    // POST /currencies/set-base
    public function setBaseCurrency(Request $request)
    {
        $code = $request->input('code');

        Currency::query()->update(['is_base' => false]);
        Currency::where('code', $code)->update(['is_base' => true]);

        return response()->json(['message' => 'Base currency updated']);
    }
  
 

    public function destroy(string $id)
    {
        $curr= Currency::findOrFail($id);
        $curr->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
