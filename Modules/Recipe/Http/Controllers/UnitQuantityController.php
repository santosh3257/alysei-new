<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\UnitQuantity;
use Validator;

class UnitQuantityController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $quantities = UnitQuantity::orderBy('created_at','desc')->paginate(10);
        return view('recipe::unitquantity.index', compact('quantities'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('recipe::unitquantity.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'name_en' => 'required',
                    'name_it' => 'required',
                    'status' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newDiet = new UnitQuantity();
            $newDiet->name_en = $request->name_en;
            $newDiet->name_it = $request->name_it;
            $newDiet->status = $request->status;
            $newDiet->save();

            $message = "Quantity added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $quantity = UnitQuantity::find($id);
        return view('recipe::unitquantity.edit', compact('quantity'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                'name_en' => 'required',
                'name_it' => 'required',
                'status' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];

            $updatedData['name_en'] = $request->name_en;
            $updatedData['name_it'] = $request->name_it;
            $updatedData['status'] = $request->status;
            
            UnitQuantity::where('id',$id)->update($updatedData);

            $message = "Quantity updated successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
