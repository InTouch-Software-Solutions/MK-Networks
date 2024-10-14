  public function accessories(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'products' => 'required',
        ]);

        $assign = AssignAccessories::create([
            'user_id' => $request->user_id,
            'products' => json_encode($request->products),

        ]);
        return response()->json($assign, 200);
    }


    public function viewaccessories()
    {
        $data = AssignAccessories::all();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);

    }


    public function assigntosalesman(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'products' => 'required',
        ]);

        $assign = Salesman::create([
            'user_id' => $request->user_id,
            'products' => json_encode($request->products),

        ]);
        return response()->json($assign, 200);
    }


    public function viewaccessories1()
    {
        $data = AssignAccessories::all();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);

    }