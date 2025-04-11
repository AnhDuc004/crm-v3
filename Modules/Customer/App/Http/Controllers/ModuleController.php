<?php

namespace Modules\Customer\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Modules\Customer\Repositories\Module\ModuleInterface;

class ModuleController extends Controller
{
    protected $moduleRepo;

    public function __construct(ModuleInterface $moduleRepo)
    {
        $this->moduleRepo = $moduleRepo;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        return $this->moduleRepo->listAll($request->all());
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return $this->moduleRepo->create($request->all());
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return $this->moduleRepo->findId($id);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return $this->moduleRepo->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return $this->moduleRepo->destroy($id);
    }
}
